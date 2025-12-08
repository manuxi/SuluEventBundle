<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
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
        private MediaManagerInterface $mediaManager,
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

        if ('publish' === $request->query->get('action')) {
            $dimensionContent = $this->contentManager->applyTransition(
                $event,
                $dimensionAttributes,
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
            );

            $this->entityManager->flush();
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

                return $this->handleView($this->view($this->normalize($event, $dimensionContent)));

            case 'unpublish':
                $dimensionContent = $this->contentManager->applyTransition(
                    $event,
                    $dimensionAttributes,
                    WorkflowInterface::WORKFLOW_TRANSITION_UNPUBLISH
                );

                $this->entityManager->flush();

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
                throw new RestException('Unrecognized action: ' . $action);
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
        $event = $this->entityManager->getReference(Event::class, $id);

        $this->entityManager->remove($event);
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
        if ($request->headers->get('Content-Type') === 'application/json') {
            return $request->toArray();
        }
        return $request->request->all();
    }

    private function setCustomData(Event $event, EventDimensionContent $dimensionContent, array $data): void
    {
        // Set Location (Unlocalized)
        // Set Location (Unlocalized + Localized)
        $locationId = null;
        if (isset($data['locationId'])) {
            $locationId = $data['locationId'];
        } elseif (isset($data['location'])) {
            $locationId = $data['location'];
        }

        if (is_array($locationId) && isset($locationId['id'])) {
            $locationId = $locationId['id'];
        }

        if ($locationId) {
            // Use find() to ensure entity exists and is loaded
            $location = $this->entityManager->find(Location::class, $locationId);

            if ($location) {

                // Find or create unlocalized content
                $unlocalizedContent = null;
                foreach ($event->getDimensionContents() as $content) {
                    if ($content->getLocale() === null && $content->getStage() === DimensionContentInterface::STAGE_DRAFT) {
                        $unlocalizedContent = $content;
                        break;
                    }
                }

                if (!$unlocalizedContent) {
                    $unlocalizedContent = new EventDimensionContent($event);
                    $unlocalizedContent->setStage(DimensionContentInterface::STAGE_DRAFT);
                    $event->addDimensionContent($unlocalizedContent);
                    $this->entityManager->persist($unlocalizedContent);
                }

                $unlocalizedContent->setLocation($location);

                // Also set on current localized content to be safe (needed for Response)
                $dimensionContent->setLocation($location);

                // FIX: $dimensionContent is detached/unmanaged here (see debug output).
                // We must load the managed entity to persist the relation change to the DB.
                if ($dimensionContent->getId()) {
                    $managedContent = $this->entityManager->find(EventDimensionContent::class, $dimensionContent->getId());
                    if ($managedContent) {
                        $managedContent->setLocation($location);
                    }
                }
            }
        }

        // Set Author (Localized)
        if (isset($data['author'])) {
            $authorId = $data['author'];
            if (is_array($authorId) && isset($authorId['id'])) {
                $authorId = $authorId['id'];
            }
            $author = $authorId ? $this->entityManager->getReference(\Sulu\Bundle\ContactBundle\Entity\Contact::class, $authorId) : null;
            $dimensionContent->setAuthor($author);
        }

        // Set Authored Date (Localized)
        if (isset($data['authored'])) {
            $authored = $data['authored'] ? new \DateTimeImmutable($data['authored']) : new \DateTimeImmutable();
            $dimensionContent->setAuthored($authored);
        }
    }

    protected function normalize(Event $event, EventDimensionContent $dimensionContent): array
    {
        return $this->contentManager->normalize($dimensionContent);
    }
}