<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Manuxi\SuluEventBundle\Common\DoctrineListRepresentationFactory;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventSeo;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\EventSeoRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
//use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
//use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("event")
 */
class EventController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    private $locationRepository;

    private $eventRepository;

    private $eventSeoRepository;

    private $mediaRepository;

    private $doctrineListRepresentationFactory;

    private $entityManager;

    public function __construct(
        EventRepository $eventRepository,
        EventSeoRepository $eventSeoRepository,
        MediaRepositoryInterface $mediaRepository,
        LocationRepository $locationRepository,
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        EntityManagerInterface $entityManager,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->eventRepository                   = $eventRepository;
        $this->eventSeoRepository                = $eventSeoRepository;
        $this->mediaRepository                   = $mediaRepository;
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->entityManager                     = $entityManager;
        $this->locationRepository                = $locationRepository;

        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $locale             = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Event::RESOURCE_KEY,
            [],
            ['locale' => $locale]
        );

        return $this->handleView($this->view($listRepresentation));
    }

    public function getAction(int $id, Request $request): Response
    {
        $entity = $this->loadEvent($id, $request);
        if (!$entity) {
            throw new NotFoundHttpException();
        }

        return $this->handleView($this->view($entity));
    }

    /**
     * @throws \Exception
     */
    public function postAction(Request $request): Response
    {
        //event
        $eventEntity = $this->createEvent($request);
        $this->mapDataToEntity($request->request->all(), $eventEntity);
        $this->saveEvent($eventEntity);

        //in the first step we'll _only_ create a Event object (no Seo, Taxonomy, etc.)

        return $this->handleView($this->view($eventEntity, 201));
    }

    /**
     * @Rest\Post("/events/{id}")
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $event = $this->eventRepository->findById($id, (string) $this->getLocale($request));
        if (!$event) {
            throw new NotFoundHttpException();
        }

        switch ($request->query->get('action')) {
            case 'enable':
                $event->setEnabled(true);
                break;
            case 'disable':
                $event->setEnabled(false);
                break;
        }

        $this->eventRepository->save($event);

        return $this->handleView($this->view($event));
    }

    /**
     * @throws \Exception
     */
    public function putAction(int $id, Request $request): Response
    {
        $eventEntity = $this->loadEvent($id, $request);
        if (!$eventEntity) {
            throw new NotFoundHttpException();
        }

        $this->mapDataToEntity($request->request->all(), $eventEntity);
        $this->saveEvent($eventEntity);

        //do we have an EventSeo?
        $eventSeoEntity = $eventEntity->getEventSeo();
        $this->mapDataToEventSeoEntity($request->request->all(), $eventSeoEntity);
        $this->saveEventSeo($eventSeoEntity);

        return $this->handleView($this->view($eventEntity));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(int $id): Response
    {
        $this->removeEvent($id);

        return $this->handleView($this->view(null, 204));
    }

    public function getSecurityContext(): string
    {
        return Event::SECURITY_CONTEXT;
    }

    /**
     * @throws \Exception
     */
    protected function mapDataToEntity(array $data, Event $entity): void
    {
        $entity->setTitle($data['title']);

        $teaser = $data['teaser'] ?? null;
        if ($teaser) {
            $entity->setTeaser($teaser);
        }

        $description = $data['description'] ?? null;
        if ($description) {
            $entity->setDescription($description);
        }

        $image   = null;
        $imageId = ($data['image']['id'] ?? null);
        if ($imageId) {
            $image = $this->mediaRepository->findMediaById($imageId);
            $entity->setImage($image);
        }

        $startDate = $data['startDate'] ?? null;
        if ($startDate) {
            $entity->setStartDate(new \DateTimeImmutable($startDate));
        }

        $endDate = $data['endDate'] ?? null;
        if ($endDate) {
            $entity->setEndDate(new \DateTimeImmutable($endDate));
        }

        $locationId = $data['locationId'] ?? null;
        if ($locationId) {
            $entity->setLocation(
                $this->locationRepository->findById((int) $locationId)
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function mapDataToEventSeoEntity(array $data, EventSeo $entity): void
    {
        $title = $data['ext']['seo']['title'] ?? null;
        $entity->setTitle($title);

        $description = $data['ext']['seo']['description'] ?? null;
        $entity->setDescription($description);

        $keywords = $data['ext']['seo']['keywords'] ?? null;
        $entity->setKeywords($keywords);

        $canonicalUrl = $data['ext']['seo']['canonicalUrl'] ?? null;
        $entity->setCanonicalUrl($canonicalUrl);

        $noIndex = $data['ext']['seo']['noIndex'] ?? false;
        $entity->setNoIndex($noIndex);

        $noFollow = $data['ext']['seo']['noFollow'] ?? false;
        $entity->setNoFollow($noFollow);

        $hideInSitemap = $data['ext']['seo']['hideInSitemap'] ?? false;
        $entity->setHideInSitemap($hideInSitemap);
    }

    protected function loadEvent(int $id, Request $request): ?Event
    {
        return $this->eventRepository->findById($id, (string) $this->getLocale($request));
    }

    protected function createEvent(Request $request): Event
    {
        return $this->eventRepository->create((string) $this->getLocale($request));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function saveEvent(Event $entity): void
    {
        $this->eventRepository->save($entity);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function removeEvent(int $id): void
    {
        $this->eventRepository->remove($id);
    }

    /** @noinspection PhpUnused */
    protected function loadEventSeo(int $id, Request $request): ?EventSeo
    {
        return $this->eventSeoRepository->findById($id, (string) $this->getLocale($request));
    }

    protected function createEventSeo(Request $request): EventSeo
    {
        return $this->eventSeoRepository->create((string) $this->getLocale($request));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function saveEventSeo(EventSeo $entity): void
    {
        $this->eventSeoRepository->save($entity);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function removeEventSeo(int $id): void
    {
        $this->eventSeoRepository->remove($id);
    }
}
