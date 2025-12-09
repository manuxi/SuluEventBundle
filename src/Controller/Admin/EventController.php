<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\CreatedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\ModifiedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\PublishedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\RemovedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\UnpublishedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\ListBuilder\DoctrineListRepresentationFactory;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin/api')]
class EventController extends AbstractRestController
{
    public function __construct(
        ViewHandlerInterface $viewHandler,
        TokenStorageInterface $tokenStorage,
        private FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private DoctrineListBuilderFactoryInterface $listBuilderFactory,
        private RestHelperInterface $restHelper,
        private ContentManagerInterface $contentManager,
        private EntityManagerInterface $entityManager,
        private DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        private DomainEventCollectorInterface $domainEventCollector,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        path: '/events.{_format}',
        name: 'sulu_event.get_events',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function cgetAction(Request $request): Response
    {
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Event::RESOURCE_KEY,
            [],
            $request->query->all()
        );

        return $this->handleView($this->view($listRepresentation));
    }

    #[Route(
        path: '/events/{id}.{_format}',
        name: 'sulu_event.get_event',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
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

    #[Route(
        path: '/events.{_format}',
        name: 'sulu_event.post_event',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['POST']
    )]
    public function postAction(Request $request): Response
    {
        $event = new Event();

        $data = $this->getData($request);
        $dimensionAttributes = $this->getDimensionAttributes($request);

        $dimensionContent = $this->contentManager->persist($event, $data, $dimensionAttributes);
        $this->setCustomData($event, $dimensionContent, $data);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->domainEventCollector->collect(new CreatedEvent($event, $data));

        if ('publish' === $request->query->get('action')) {
            $dimensionContent = $this->contentManager->applyTransition(
                $event,
                $dimensionAttributes,
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
            );

            $this->entityManager->flush();
            $this->domainEventCollector->collect(new PublishedEvent($event, $data));
        }

        return $this->handleView($this->view($this->normalize($event, $dimensionContent), 201));
    }

    #[Route(
        path: '/events/{id}.{_format}',
        name: 'sulu_event.post_event_trigger',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['POST']
    )]
    public function postTriggerAction(string $id, Request $request): Response
    {
        /** @var Event|null $event */
        $event = $this->entityManager->getRepository(Event::class)->findOneBy(['id' => $id]);

        if (!$event) {
            throw new NotFoundHttpException();
        }

        $dimensionAttributes = $this->getDimensionAttributes($request);
        $action = $request->query->get('action');

        switch ($action) {
            case 'copy_locale':
                $dimensionContent = $this->contentManager->copy(
                    $event,
                    [
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $request->query->get('src'),
                    ],
                    $event,
                    [
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $request->query->get('dest'),
                    ]
                );

                $this->entityManager->flush();

                // Assuming copy_locale modifies the event in the destination
                // We could dispatch ModifiedEvent here but it requires context.
                // For now, let's leave it or add specific events if needed.

                return $this->handleView($this->view($this->normalize($event, $dimensionContent)));

            case 'unpublish':
                $dimensionContent = $this->contentManager->applyTransition(
                    $event,
                    $dimensionAttributes,
                    WorkflowInterface::WORKFLOW_TRANSITION_UNPUBLISH
                );

                $this->entityManager->flush();
                $this->domainEventCollector->collect(new UnpublishedEvent($event, $request->query->all()));

                return $this->handleView($this->view($this->normalize($event, $dimensionContent)));

            case 'remove_draft':
                $dimensionContent = $this->contentManager->applyTransition(
                    $event,
                    $dimensionAttributes,
                    WorkflowInterface::WORKFLOW_TRANSITION_REMOVE_DRAFT
                );

                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($event, $dimensionContent)));

            case 'restore':
                $version = (int) $request->query->get('version');
                $dimensionContent = $this->contentManager->copy(
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

                return $this->handleView($this->view($this->normalize($event, $dimensionContent)));

            default:
                throw new RestException('Unrecognized action: '.$action);
        }
    }

    #[Route(
        path: '/events/{id}.{_format}',
        name: 'sulu_event.put_event',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
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
        $this->setCustomData($event, $dimensionContent, $data);

        if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace()) {
            $dimensionContent = $this->contentManager->applyTransition(
                $event,
                $dimensionAttributes,
                WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT
            );
        }

        $this->entityManager->flush();
        $this->domainEventCollector->collect(new ModifiedEvent($event, $data));

        if ('publish' === $request->query->get('action')) {
            $dimensionContent = $this->contentManager->applyTransition(
                $event,
                $dimensionAttributes,
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
            );

            $this->entityManager->flush();
            $this->domainEventCollector->collect(new PublishedEvent($event, $data));
        }

        return $this->handleView($this->view($this->normalize($event, $dimensionContent)));
    }

    #[Route(
        path: '/events/{id}.{_format}',
        name: 'sulu_event.delete_event',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['DELETE']
    )]
    public function deleteAction(int $id): Response
    {
        /** @var Event $event */
        $event = $this->entityManager->find(Event::class, $id);

        if (!$event) {
            throw new NotFoundHttpException();
        }

        $eventId = $event->getId();
        // Trying to get a title if possible, otherwise empty
        $eventTitle = '';

        $this->entityManager->remove($event);
        $this->domainEventCollector->collect(new RemovedEvent($eventId, $eventTitle));
        $this->entityManager->flush();

        return new Response('', 204);
    }

    #[Route(
        path: '/events/{id}/versions.{_format}',
        name: 'sulu_event.get_event_versions',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
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

    protected function getDimensionAttributes(Request $request): array
    {
        return $request->query->all();
    }

    protected function getData(Request $request): array
    {
        if ('application/json' === $request->headers->get('Content-Type')) {
            return $request->toArray();
        }

        return $request->request->all();
    }

    private function setCustomData(Event $event, EventDimensionContent $dimensionContent, array $data): void
    {
        $locale = $dimensionContent->getLocale();
        $stage = $dimensionContent->getStage() ?? DimensionContentInterface::STAGE_DRAFT;

        // Find the ACTUAL managed localized DimensionContent from the Event's collection
        $localizedContent = null;
        foreach ($event->getDimensionContents() as $dc) {
            if ($dc->getLocale() === $locale && $dc->getStage() === $stage) {
                $localizedContent = $dc;
                break;
            }
        }

        // Find or create unlocalized content
        $unlocalizedContent = null;
        foreach ($event->getDimensionContents() as $dc) {
            if (null === $dc->getLocale() && DimensionContentInterface::STAGE_DRAFT === $dc->getStage()) {
                $unlocalizedContent = $dc;
                break;
            }
        }

        if (!$unlocalizedContent) {
            $unlocalizedContent = new EventDimensionContent($event);
            $unlocalizedContent->setStage(DimensionContentInterface::STAGE_DRAFT);
            $event->addDimensionContent($unlocalizedContent);
            $this->entityManager->persist($unlocalizedContent);
        }

        // === SET UNLOCALIZED FIELDS ON BOTH ===

        // Location
        $locationId = $data['locationId'] ?? $data['location'] ?? null;
        if (is_array($locationId) && isset($locationId['id'])) {
            $locationId = $locationId['id'];
        }
        if ($locationId) {
            $location = $this->entityManager->find(Location::class, $locationId);
            if ($location) {
                $unlocalizedContent->setLocation($location);
                if ($localizedContent) {
                    $localizedContent->setLocation($location);
                }
            }
        }

        // Type
        if (isset($data['type'])) {
            $unlocalizedContent->setType($data['type']);
            if ($localizedContent) {
                $localizedContent->setType($data['type']);
            }
        }

        // StartDate
        if (isset($data['startDate'])) {
            $startDate = $data['startDate'] ? new \DateTimeImmutable($data['startDate']) : null;
            $unlocalizedContent->setStartDate($startDate);
            if ($localizedContent) {
                $localizedContent->setStartDate($startDate);
            }
        }

        // EndDate
        if (isset($data['endDate'])) {
            $endDate = $data['endDate'] ? new \DateTimeImmutable($data['endDate']) : null;
            $unlocalizedContent->setEndDate($endDate);
            if ($localizedContent) {
                $localizedContent->setEndDate($endDate);
            }
        }

        // Email
        if (isset($data['email'])) {
            $unlocalizedContent->setEmail($data['email']);
            if ($localizedContent) {
                $localizedContent->setEmail($data['email']);
            }
        }

        // PhoneNumber
        if (isset($data['phoneNumber'])) {
            $unlocalizedContent->setPhoneNumber($data['phoneNumber']);
            if ($localizedContent) {
                $localizedContent->setPhoneNumber($data['phoneNumber']);
            }
        }

        // === LOCALIZED FIELDS (only on localized content) ===

        if ($localizedContent) {
            // Author
            if (isset($data['author'])) {
                $authorId = $data['author'];
                if (is_array($authorId) && isset($authorId['id'])) {
                    $authorId = $authorId['id'];
                }
                $author = $authorId ? $this->entityManager->getReference(ContactInterface::class, $authorId) : null;
                $localizedContent->setAuthor($author);
            }

            // Authored Date
            if (isset($data['authored'])) {
                $authored = $data['authored'] ? new \DateTimeImmutable($data['authored']) : new \DateTimeImmutable();
                $localizedContent->setAuthored($authored);
            }
        }
    }

    protected function normalize(Event $event, EventDimensionContent $dimensionContent): array
    {
        return $this->contentManager->normalize($dimensionContent);
    }
}
