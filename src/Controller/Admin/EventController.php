<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventRecurrence;
use Manuxi\SuluEventBundle\Entity\EventSocialSettings;
use Manuxi\SuluEventBundle\Entity\Models\EventExcerptModel;
use Manuxi\SuluEventBundle\Entity\Models\EventModel;
use Manuxi\SuluEventBundle\Entity\Models\EventSeoModel;
use Manuxi\SuluEventBundle\ListBuilder\DoctrineListRepresentationFactory;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\MissingParameterException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin/api')]
class EventController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use RequestParametersTrait;

    public function __construct(
        private readonly EventModel $eventModel,
        private readonly EventSeoModel $eventSeoModel,
        private readonly EventExcerptModel $eventExcerptModel,
        private readonly DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        private readonly SecurityCheckerInterface $securityChecker,
        private readonly TrashManagerInterface $trashManager,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        '/events.{_format}',
        name: 'sulu_event.get_events',
        requirements: [
            'id' => '\d+',
            '_format' => 'json|csv',
        ],
        options: ['expose' => true],
        defaults: [
            '_format' => 'json',
        ],
        methods: ['GET']
    )]
    public function cgetAction(Request $request): Response
    {
        $locale = $request->query->get('locale');
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
    #[Route(
        '/events/{id}.{_format}',
        name: 'sulu_event.get_event',
        requirements: [
            'id' => '\d+',
            '_format' => 'json|csv',
        ],
        options: ['expose' => true],
        defaults: [
            '_format' => 'json',
        ],
        methods: ['GET']
    )]
    public function getAction(int $id, Request $request): Response
    {
        $event = $this->eventModel->getEvent($id, $request);

        return $this->handleView($this->view($event));
    }

    /**
     * @throws EntityNotFoundException
     */
    #[Route(
        '/events.{_format}',
        name: 'sulu_event.post_event',
        requirements: ['_format' => 'json'],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['POST']
    )]
    public function postAction(Request $request): Response
    {
        $entity = $this->eventModel->createEvent($request);

        return $this->handleView($this->view($entity, 201));
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(
        '/events/{id}.{_format}',
        name: 'sulu_event.put_event',
        requirements: [
            'id' => '\d+',
            '_format' => 'json',
        ],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putAction(int $id, Request $request): Response
    {
        try {
            $action = $this->getRequestParameter($request, 'action', true);
            try {
                $entity = match ($action) {
                    'publish' => $this->eventModel->publish($id, $request),
                    'draft', 'unpublish' => $this->eventModel->unpublish($id, $request),
                    default => throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action)),
                };
            } catch (RestException $exc) {
                $view = $this->view($exc->toArray(), 400);

                return $this->handleView($view);
            }
        } catch (MissingParameterException $e) {
            $entity = $this->eventModel->updateEvent($id, $request);

            $this->eventSeoModel->updateEventSeo($entity->getEventSeo(), $request);
            $this->eventExcerptModel->updateEventExcerpt($entity->getEventExcerpt(), $request);
        }

        return $this->handleView($this->view($entity));
    }

    /**
     * @throws EntityNotFoundException
     */
    #[Route(
        '/events/{id}.{_format}',
        name: 'sulu_event.delete_event',
        requirements: [
            'id' => '\d+',
            '_format' => 'json',
        ],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['DELETE']
    )]
    public function deleteAction(int $id, Request $request): Response
    {
        $entity = $this->eventModel->getEvent($id, $request);

        $this->trashManager->store(Event::RESOURCE_KEY, $entity);

        $this->eventModel->deleteEvent($entity);

        return $this->handleView($this->view(null, 204));
    }

    /**
     * @throws ORMException|OptimisticLockException|EntityNotFoundException|MissingParameterException
     */
    #[Route(
        '/events/{id}.{_format}',
        name: 'sulu_event.post_event_trigger',
        requirements: [
            'id' => '\d+',
            '_format' => 'json|csv',
        ],
        options: ['expose' => true],
        defaults: [
            '_format' => 'json',
        ],
        methods: ['POST']
    )]
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);

        try {
            switch ($action) {
                case 'publish':
                    $entity = $this->eventModel->publish($id, $request);
                    break;
                case 'draft':
                case 'unpublish':
                    $entity = $this->eventModel->unpublish($id, $request);
                    break;
                case 'copy':
                    $entity = $this->eventModel->copy($id, $request);
                    break;
                case 'copy-locale':
                    $locale = $this->getRequestParameter($request, 'locale', true);
                    $srcLocale = $this->getRequestParameter($request, 'src', false, $locale);
                    $destLocales = $this->getRequestParameter($request, 'dest', true);
                    $destLocales = explode(',', $destLocales);

                    foreach ($destLocales as $destLocale) {
                        $this->securityChecker->checkPermission(
                            new SecurityCondition($this->getSecurityContext(), $destLocale),
                            PermissionTypes::EDIT
                        );
                    }

                    $entity = $this->eventModel->copyLanguage($id, $request, $srcLocale, $destLocales);
                    break;
                default:
                    throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action));
            }
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);

            return $this->handleView($view);
        }

        return $this->handleView($this->view($entity));
    }

    /**
     * Get social settings for an event.
     */
    #[Route(
        '/events/{id}/social.{_format}',
        name: 'sulu_event.get_event_social',
        requirements: [
            'id' => '\d+',
            '_format' => 'json',
        ],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getSocialAction(int $id, Request $request): Response
    {
        $entity = $this->eventModel->getEvent($id, $request);

        if (!$entity) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        $socialSettings = $entity->getSocialSettings();

        return $this->handleView($this->view([
            'id' => $entity->getId(),
            'enableSharing' => $socialSettings?->getEnableSharing() ?? false,
            'platforms' => $socialSettings?->getPlatforms() ?? [],
            'facebookUrl' => $socialSettings?->getFacebookUrl(),
            'twitterHandle' => $socialSettings?->getTwitterHandle(),
            'instagramUrl' => $socialSettings?->getInstagramUrl(),
            'linkedinUrl' => $socialSettings?->getLinkedinUrl(),
            'customShareText' => $socialSettings?->getCustomShareText(),
            'targetGroups' => $socialSettings?->getTargetGroups(),
        ]));
    }

    /**
     * Update social settings for an event.
     */
    #[Route(
        '/events/{id}/social.{_format}',
        name: 'sulu_event.put_event_social',
        requirements: [
            'id' => '\d+',
            '_format' => 'json',
        ],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putSocialAction(int $id, Request $request): Response
    {
        $data = $request->toArray();

        $entity = $this->eventModel->getEvent($id, $request);

        if (!$entity) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        $socialSettings = $entity->getSocialSettings();
        if (!$socialSettings) {
            $socialSettings = new EventSocialSettings();
            $entity->setSocialSettings($socialSettings);
        }

        // Map data to entity
        $socialSettings->setEnableSharing($data['enableSharing'] ?? false);
        $socialSettings->setPlatforms($data['platforms'] ?? []);
        $socialSettings->setFacebookUrl($data['facebookUrl'] ?? null);
        $socialSettings->setTwitterHandle($data['twitterHandle'] ?? null);
        $socialSettings->setInstagramUrl($data['instagramUrl'] ?? null);
        $socialSettings->setLinkedinUrl($data['linkedinUrl'] ?? null);
        $socialSettings->setCustomShareText($data['customShareText'] ?? null);
        $socialSettings->setTargetGroups($data['targetGroups'] ?? null);

        //$this->entityManager->flush();

        return $this->handleView($this->view([
            'id' => $entity->getId(),
            'enableSharing' => $socialSettings->getEnableSharing(),
            'platforms' => $socialSettings->getPlatforms(),
            'facebookUrl' => $socialSettings->getFacebookUrl(),
            'twitterHandle' => $socialSettings->getTwitterHandle(),
            'instagramUrl' => $socialSettings->getInstagramUrl(),
            'linkedinUrl' => $socialSettings->getLinkedinUrl(),
            'customShareText' => $socialSettings->getCustomShareText(),
            'targetGroups' => $socialSettings->getTargetGroups(),
        ]));
    }

    /**
     * Get recurrence settings for an event
     */
    #[Route(
        '/events/{id}/recurrence.{_format}',
        name: 'sulu_event.get_event_recurrence',
        requirements: [
            'id' => '\d+',
            '_format' => 'json'
        ],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getRecurrenceAction(int $id, Request $request): Response
    {
        $entity = $this->eventModel->getEvent($id, $request);

        if (!$entity) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        $recurrence = $entity->getRecurrence();

        return $this->handleView($this->view([
            'id' => $entity->getId(),
            'isRecurring' => $recurrence?->getIsRecurring() ?? false,
            'frequency' => $recurrence?->getFrequency(),
            'interval' => $recurrence?->getInterval() ?? 1,
            'byWeekday' => $recurrence?->getByWeekday() ?? [],
            'endType' => $recurrence?->getEndType() ?? 'never',
            'count' => $recurrence?->getCount(),
            'until' => $recurrence?->getUntil()?->format('Y-m-d'),
        ]));
    }

    /**
     * Update recurrence settings for an event
     */
    #[Route(
        '/events/{id}/recurrence.{_format}',
        name: 'sulu_event.put_event_recurrence',
        requirements: [
            'id' => '\d+',
            '_format' => 'json'
        ],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putRecurrenceAction(int $id, Request $request): Response
    {
        $data = $request->toArray();

        $entity = $this->eventModel->getEvent($id, $request);

        if (!$entity) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        $recurrence = $entity->getRecurrence();
        if (!$recurrence) {
            $recurrence = new EventRecurrence();
            $entity->setRecurrence($recurrence);
        }

        // Map data to entity
        $recurrence->setIsRecurring($data['isRecurring'] ?? false);
        $recurrence->setFrequency($data['frequency'] ?? null);
        $recurrence->setInterval($data['interval'] ?? 1);
        $recurrence->setByWeekday($data['byWeekday'] ?? []);
        $recurrence->setEndType($data['endType'] ?? 'never');
        $recurrence->setCount($data['count'] ?? null);

        if (!empty($data['until'])) {
            $recurrence->setUntil(new \DateTime($data['until']));
        } else {
            $recurrence->setUntil(null);
        }

        //$this->entityManager->flush();

        return $this->handleView($this->view([
            'id' => $entity->getId(),
            'isRecurring' => $recurrence->getIsRecurring(),
            'frequency' => $recurrence->getFrequency(),
            'interval' => $recurrence->getInterval(),
            'byWeekday' => $recurrence->getByWeekday(),
            'endType' => $recurrence->getEndType(),
            'count' => $recurrence->getCount(),
            'until' => $recurrence->getUntil()?->format('Y-m-d'),
        ]));
    }

    public function getSecurityContext(): string
    {
        return Event::SECURITY_CONTEXT;
    }
}
