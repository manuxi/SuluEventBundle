<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventRecurrence;
use Manuxi\SuluEventBundle\Entity\EventSocialSettings;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route(path: '/admin/api')]
class EventController extends AbstractRestController
{
    use RequestParametersTrait;

    private FieldDescriptorFactoryInterface $fieldDescriptorFactory;
    private DoctrineListBuilderFactoryInterface $listBuilderFactory;
    private RestHelperInterface $restHelper;
    private ContentManagerInterface $contentManager;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        TokenStorageInterface $tokenStorage,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        DoctrineListBuilderFactoryInterface $listBuilderFactory,
        RestHelperInterface $restHelper,
        ContentManagerInterface $contentManager,
        EntityManagerInterface $entityManager,
    ) {
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->listBuilderFactory = $listBuilderFactory;
        $this->restHelper = $restHelper;
        $this->contentManager = $contentManager;
        $this->entityManager = $entityManager;

        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(path: '/events.{_format}', defaults: ['_format' => 'json'], methods: ['GET'])]
    public function cgetAction(Request $request): Response
    {
        /** @var DoctrineFieldDescriptorInterface[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors(Event::RESOURCE_KEY);
        /** @var DoctrineListBuilder $listBuilder */
        $listBuilder = $this->listBuilderFactory->create(Event::class);
        $listBuilder->addSelectField($fieldDescriptors['locale']);
        $listBuilder->addSelectField($fieldDescriptors['ghostLocale']);
        $listBuilder->setParameter('locale', $request->query->get('locale'));
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listRepresentation = new PaginatedRepresentation(
            $listBuilder->execute(),
            Event::RESOURCE_KEY,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            $listBuilder->count()
        );

        return $this->handleView($this->view($listRepresentation));
    }

    #[Route(path: '/events/{id}/versions.{_format}', defaults: ['_format' => 'json'], methods: ['GET'])]
    public function getVersionsAction(Request $request, string $id): Response
    {
        $locale = $request->query->get('locale');

        /** @var DoctrineFieldDescriptorInterface[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors('events_versions');
        /** @var DoctrineListBuilder $listBuilder */
        $listBuilder = $this->listBuilderFactory->create(Event::class);
        $listBuilder->setParameter('locale', $locale);
        $listBuilder->setParameter('id', $id);
        $listBuilder->setIdField($fieldDescriptors['id']);
        $listBuilder->sort($fieldDescriptors['version'], 'DESC');
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $result = $listBuilder->execute();
        $listRepresentation = new PaginatedRepresentation(
            $result,
            'events_versions',
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            $listBuilder->count(),
        );

        return $this->handleView($this->view($listRepresentation));
    }

    #[Route(path: '/events/{id}.{_format}', defaults: ['_format' => 'json'], methods: ['GET'])]
    public function getAction(Request $request, int $id): Response
    {
        /** @var Event|null $event */
        $event = $this->entityManager->getRepository(Event::class)->findOneBy(['id' => $id]);

        if (!$event) {
            throw new NotFoundHttpException();
        }

        $dimensionAttributes = $this->getDimensionAttributes($request);
        $dimensionContent = $this->contentManager->resolve($event, $dimensionAttributes);

        return $this->handleView($this->view($this->normalize($event, $dimensionContent)));
    }

    #[Route(path: '/events.{_format}', defaults: ['_format' => 'json'], methods: ['POST'])]
    public function postAction(Request $request): Response
    {
        $event = new Event();
        $this->entityManager->persist($event);

        $dimensionAttributes = $this->getDimensionAttributes($request);
        $data = $this->getData($request);

        /** @var EventDimensionContent $dimensionContent */
        $dimensionContent = $this->contentManager->persist($event, $data, $dimensionAttributes);
        $this->entityManager->flush();

        return $this->handleView($this->view($this->normalize($event, $dimensionContent)), 201);
    }

    #[Route(path: '/events/{id}.{_format}', defaults: ['_format' => 'json'], methods: ['POST'])]
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);

        /** @var Event|null $event */
        $event = $this->entityManager->getRepository(Event::class)->findOneBy(['id' => $id]);

        if (!$event) {
            throw new NotFoundHttpException();
        }

        $dimensionAttributes = $this->getDimensionAttributes($request);

        switch ($action) {
            /*case 'publish':
                $dimensionContent = $this->contentManager->applyTransition(
                    $event,
                    $dimensionAttributes,
                    WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
                );

                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($event, $dimensionContent)));
            case 'unpublish':
                $dimensionContent = $this->contentManager->applyTransition(
                    $event,
                    $dimensionAttributes,
                    WorkflowInterface::WORKFLOW_TRANSITION_UNPUBLISH
                );

                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($event, $dimensionContent)));*/
            case 'copy-locale':
                $srcLocale = $this->getRequestParameter($request, 'src', true);
                $destLocale = $this->getRequestParameter($request, 'dest', true);

                $data = $this->contentManager->normalize($event, ['locale' => $srcLocale]);
                $dimensionContent = $this->contentManager->persist($event, $data, ['locale' => $destLocale, 'stage' => 'draft']);
                return $this->handleView($this->view($this->normalize($event, $dimensionContent)));

            case 'restore-version':
                /*$version = (int) $this->getRequestParameter($request, 'version', true);

                $dimensionContent = $this->contentManager->restoreVersion(
                    $event,
                    [
                        'stage' => $dimensionAttributes['stage'] ?? DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $dimensionAttributes['locale'] ?? null,
                        'version' => $version,
                    ],
                    $event,
                    [
                        'stage' => $dimensionAttributes['stage'] ?? DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $dimensionAttributes['locale'] ?? null,
                        'version' => DimensionContentInterface::CURRENT_VERSION,
                    ],
                    [
                        'ignoredAttributes' => ['url'],
                    ]
                );

                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($event, $dimensionContent)));*/
                return $this->handleView(
                    $this->view(null, 501)
                );
            default:
                throw new \RuntimeException('Unrecognized action: '.$action);
        }
    }

    #[Route(path: '/events/{id}.{_format}', defaults: ['_format' => 'json'], methods: ['PUT'])]
    public function putAction(Request $request, int $id): Response
    {
        /** @var Event|null $event */
        $event = $this->entityManager->getRepository(Event::class)->findOneBy(['id' => $id]);

        if (!$event) {
            throw new NotFoundHttpException();
        }

        $data = $this->getData($request);
        $dimensionAttributes = $this->getDimensionAttributes($request);

        /** @var EventDimensionContent $dimensionContent */
        $dimensionContent = $this->contentManager->persist($event, $data, $dimensionAttributes);
        if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace()) {
            $dimensionContent = $this->contentManager->applyTransition(
                $event,
                $dimensionAttributes,
                WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT
            );
        }

        $this->entityManager->flush();

        if ('publish' === $request->query->get('action')) {
            $dimensionContent = $this->contentManager->applyTransition(
                $event,
                $dimensionAttributes,
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
            );

            $this->entityManager->flush();
        }

        return $this->handleView($this->view($this->normalize($event, $dimensionContent)));
    }

    #[Route(path: '/events/{id}.{_format}', defaults: ['_format' => 'json'], methods: ['DELETE'])]
    public function deleteAction(int $id): Response
    {
        /** @var Event $event */
        $event = $this->entityManager->getReference(Event::class, $id);

        $this->entityManager->remove($event);
        $this->entityManager->flush();

        return new Response('', 204);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDimensionAttributes(Request $request): array
    {
        return $request->query->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(Request $request): array
    {
        return $request->request->all();
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
        /** @var Event $event */
        $event = $this->entityManager->getReference(Event::class, $id);

        if (!$event) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        $socialSettings = $event->getSocialSettings();

        return $this->handleView($this->view([
            'id' => $event->getId(),
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

        /** @var Event $event */
        $event = $this->entityManager->getReference(Event::class, $id);

        if (!$event) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        $socialSettings = $event->getSocialSettings();
        if (!$socialSettings) {
            $socialSettings = new EventSocialSettings();
            $event->setSocialSettings($socialSettings);
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
            'id' => $event->getId(),
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
        /** @var Event $event */
        $event = $this->entityManager->getReference(Event::class, $id);

        if (!$event) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        $recurrence = $event->getRecurrence();

        return $this->handleView($this->view([
            'id' => $event->getId(),
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

        /** @var Event $event */
        $event = $this->entityManager->getReference(Event::class, $id);

        if (!$event) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        $recurrence = $event->getRecurrence();
        if (!$recurrence) {
            $recurrence = new EventRecurrence();
            $event->setRecurrence($recurrence);
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

        return $this->handleView($this->view([
            'id' => $event->getId(),
            'isRecurring' => $recurrence->getIsRecurring(),
            'frequency' => $recurrence->getFrequency(),
            'interval' => $recurrence->getInterval(),
            'byWeekday' => $recurrence->getByWeekday(),
            'endType' => $recurrence->getEndType(),
            'count' => $recurrence->getCount(),
            'until' => $recurrence->getUntil()?->format('Y-m-d'),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalize(Event $event, EventDimensionContent $dimensionContent): array
    {
        $normalizedContent = $this->contentManager->normalize($dimensionContent);

        return $normalizedContent;
    }
}
