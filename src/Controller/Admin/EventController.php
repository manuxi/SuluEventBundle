<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Manuxi\SuluEventBundle\Common\DoctrineListRepresentationFactory;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Models\EventExcerptModel;
use Manuxi\SuluEventBundle\Entity\Models\EventModel;
use Manuxi\SuluEventBundle\Entity\Models\EventSeoModel;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
//use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
//use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("event")
 */
class EventController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    private $eventModel;
    private $eventSeoModel;
    private $eventExcerptModel;
    private $doctrineListRepresentationFactory;
    private $routeManager;
    private $routeRepository;

    public function __construct(
        EventModel $eventModel,
        EventSeoModel $eventSeoModel,
        EventExcerptModel $eventExcerptModel,
        RouteManagerInterface $routeManager,
        RouteRepositoryInterface $routeRepository,
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        parent::__construct($viewHandler, $tokenStorage);
        $this->eventModel                        = $eventModel;
        $this->eventSeoModel                     = $eventSeoModel;
        $this->eventExcerptModel                 = $eventExcerptModel;
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->routeManager                      = $routeManager;
        $this->routeRepository                   = $routeRepository;
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

    /**
     * @throws EntityNotFoundException
     */
    public function getAction(int $id, Request $request): Response
    {
        $event = $this->eventModel->getEvent($id, $request);
        return $this->handleView($this->view($event));
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postAction(Request $request): Response
    {
        $event = $this->eventModel->createEvent($request);
        $this->updateRoutesForEntity($event);

        return $this->handleView($this->view($event, 201));
    }

    /**
     * @Rest\Post("/events/{id}")
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EntityNotFoundException
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $event = $this->eventModel->enableEvent($id, $request);
        return $this->handleView($this->view($event));
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function putAction(int $id, Request $request): Response
    {
        $event = $this->eventModel->updateEvent($id, $request);
        $this->updateRoutesForEntity($event);


        $this->eventSeoModel->updateEventSeo($event->getEventSeo(), $request);
        $this->eventExcerptModel->updateEventExcerpt($event->getEventExcerpt(), $request);

//        dd($request->request->all());

        return $this->handleView($this->view($event));
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(int $id): Response
    {
        $event = $this->eventModel->getEvent($id);
        $this->removeRoutesForEntity($event);

        $this->eventModel->deleteEvent($id);
        return $this->handleView($this->view(null, 204));
    }

    public function getSecurityContext(): string
    {
        return Event::SECURITY_CONTEXT;
    }

    protected function updateRoutesForEntity(Event $entity): void
    {
        $this->routeManager->createOrUpdateByAttributes(
            Event::class,
            (string) $entity->getId(),
            $entity->getLocale(),
            $entity->getRoutePath()
        );
    }

    protected function removeRoutesForEntity(Event $entity): void
    {
        $routes = $this->routeRepository->findAllByEntity(
            Event::class,
            (string) $entity->getId(),
            $entity->getLocale()
        );

        foreach ($routes as $route) {
            $this->routeRepository->remove($route);
        }
    }
}
