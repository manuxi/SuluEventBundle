<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventRecurrence;
use Manuxi\SuluEventBundle\Entity\EventSocialSettings;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/admin/api')]
class EventController extends AbstractFOSRestController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentManagerInterface $contentManager,
    ) {
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
        // List action is handled by Sulu's ListBuilder (events.xml)
        return new Response('', 501);
    }

    #[Route(
        path: '/events/{id}.{_format}',
        name: 'sulu_event.get_event',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getAction(int $id, Request $request): Response
    {
        /** @var Event|null $event */
        $event = $this->entityManager->getRepository(Event::class)->find($id);

        if (!$event) {
            throw new NotFoundHttpException();
        }

        $dimensionAttributes = $this->getDimensionAttributes($request);

        /** @var EventDimensionContent $dimensionContent */
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
        $this->entityManager->persist($event);

        $dimensionAttributes = $this->getDimensionAttributes($request);
        $data = $this->getData($request);

        /** @var EventDimensionContent $dimensionContent */
        $dimensionContent = $this->contentManager->persist($event, $data, $dimensionAttributes);
        $this->entityManager->flush();

        return $this->handleView($this->view($this->normalize($event, $dimensionContent), 201));
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
        name: 'sulu_event.post_event_trigger',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['POST']
    )]
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);

        /** @var Event|null $event */
        $event = $this->entityManager->getRepository(Event::class)->findOneBy(['id' => $id]);

        if (!$event) {
            throw new NotFoundHttpException();
        }

        switch ($action) {
            case 'copy-locale':
                $srcLocale = $this->getRequestParameter($request, 'src', true);
                $destLocale = $this->getRequestParameter($request, 'dest', true);

                $data = $this->contentManager->normalize($event, ['locale' => $srcLocale]);
                $dimensionContent = $this->contentManager->persist($event, $data, ['locale' => $destLocale, 'stage' => 'draft']);

                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($event, $dimensionContent)));

            default:
                throw new \RuntimeException('Unrecognized action: '.$action);
        }
    }

    #[Route(path: '/events/{id}.{_format}', defaults: ['_format' => 'json'], methods: ['DELETE'])]
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

    /**
     * Get social settings for an event.
     */
    #[Route(
        '/events/{id}/social.{_format}',
        name: 'sulu_event.get_event_social',
        requirements: ['id' => '\d+', '_format' => 'json'],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getSocialAction(int $id, Request $request): Response
    {
        /** @var Event $event */
        $event = $this->entityManager->find(Event::class, $id);

        if (!$event) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        // Get unlocalized dimension content (where socialSettings lives)
        $unlocalizedDimensionContent = $this->getUnlocalizedDimensionContent($event);

        if (!$unlocalizedDimensionContent) {
            throw new \RuntimeException('No unlocalized dimension content found for event');
        }

        $socialSettings = $unlocalizedDimensionContent->getSocialSettings();

        return $this->handleView($this->view([
            'id' => $event->getId(),
            'twitterShareText' => $socialSettings?->getTwitterShareText(),
            'facebookShareText' => $socialSettings?->getFacebookShareText(),
            'linkedInShareText' => $socialSettings?->getLinkedInShareText(),
            'emailShareSubject' => $socialSettings?->getEmailShareSubject(),
            'emailShareBody' => $socialSettings?->getEmailShareBody(),
        ]));
    }

    /**
     * Update social settings for an event.
     */
    #[Route(
        '/events/{id}/social.{_format}',
        name: 'sulu_event.put_event_social',
        requirements: ['id' => '\d+', '_format' => 'json'],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putSocialAction(int $id, Request $request): Response
    {
        $data = $request->toArray();

        /** @var Event $event */
        $event = $this->entityManager->find(Event::class, $id);

        if (!$event) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        // Get unlocalized dimension content (where socialSettings lives)
        $unlocalizedDimensionContent = $this->getUnlocalizedDimensionContent($event);

        if (!$unlocalizedDimensionContent) {
            throw new \RuntimeException('No unlocalized dimension content found for event');
        }

        $socialSettings = $unlocalizedDimensionContent->getSocialSettings();
        if (!$socialSettings) {
            $socialSettings = new EventSocialSettings($unlocalizedDimensionContent);
            $unlocalizedDimensionContent->setSocialSettings($socialSettings);
        }

        // Map data to entity
        $socialSettings->setTwitterShareText($data['twitterShareText'] ?? null);
        $socialSettings->setFacebookShareText($data['facebookShareText'] ?? null);
        $socialSettings->setLinkedInShareText($data['linkedInShareText'] ?? null);
        $socialSettings->setEmailShareSubject($data['emailShareSubject'] ?? null);
        $socialSettings->setEmailShareBody($data['emailShareBody'] ?? null);

        $this->entityManager->flush();

        return $this->handleView($this->view([
            'id' => $event->getId(),
            'twitterShareText' => $socialSettings->getTwitterShareText(),
            'facebookShareText' => $socialSettings->getFacebookShareText(),
            'linkedInShareText' => $socialSettings->getLinkedInShareText(),
            'emailShareSubject' => $socialSettings->getEmailShareSubject(),
            'emailShareBody' => $socialSettings->getEmailShareBody(),
        ]));
    }

    /**
     * Get recurrence settings for an event.
     */
    #[Route(
        '/events/{id}/recurrence.{_format}',
        name: 'sulu_event.get_event_recurrence',
        requirements: ['id' => '\d+', '_format' => 'json'],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getRecurrenceAction(int $id, Request $request): Response
    {
        /** @var Event $event */
        $event = $this->entityManager->find(Event::class, $id);

        if (!$event) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        // Get unlocalized dimension content (where recurrence lives)
        $unlocalizedDimensionContent = $this->getUnlocalizedDimensionContent($event);

        if (!$unlocalizedDimensionContent) {
            throw new \RuntimeException('No unlocalized dimension content found for event');
        }

        $recurrence = $unlocalizedDimensionContent->getRecurrence();

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
     * Update recurrence settings for an event.
     */
    #[Route(
        '/events/{id}/recurrence.{_format}',
        name: 'sulu_event.put_event_recurrence',
        requirements: ['id' => '\d+', '_format' => 'json'],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putRecurrenceAction(int $id, Request $request): Response
    {
        $data = $request->toArray();

        /** @var Event $event */
        $event = $this->entityManager->find(Event::class, $id);

        if (!$event) {
            throw new EntityNotFoundException(Event::class, $id);
        }

        // Get unlocalized dimension content (where recurrence lives)
        $unlocalizedDimensionContent = $this->getUnlocalizedDimensionContent($event);

        if (!$unlocalizedDimensionContent) {
            throw new \RuntimeException('No unlocalized dimension content found for event');
        }

        $recurrence = $unlocalizedDimensionContent->getRecurrence();
        if (!$recurrence) {
            $recurrence = new EventRecurrence($unlocalizedDimensionContent);
            $unlocalizedDimensionContent->setRecurrence($recurrence);
        }

        // Map data to entity
        $recurrence->setIsRecurring($data['isRecurring'] ?? false);
        $recurrence->setFrequency($data['frequency'] ?? null);
        $recurrence->setInterval($data['interval'] ?? 1);
        $recurrence->setByWeekday($data['byWeekday'] ?? []);
        $recurrence->setEndType($data['endType'] ?? 'never');
        $recurrence->setCount($data['count'] ?? null);

        if (isset($data['until'])) {
            $recurrence->setUntil(new \DateTime($data['until']));
        }

        $this->entityManager->flush();

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
     * Get unlocalized dimension content from event.
     */
    private function getUnlocalizedDimensionContent(Event $event): ?EventDimensionContent
    {
        foreach ($event->getDimensionContents() as $dc) {
            if (null === $dc->getLocale()
                && DimensionContentInterface::STAGE_DRAFT === $dc->getStage()
                && DimensionContentInterface::CURRENT_VERSION === $dc->getVersion()
            ) {
                return $dc;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalize(Event $event, EventDimensionContent $dimensionContent): array
    {
        $data = [
            'id' => $event->getId(),
            'template' => $dimensionContent->getTemplateKey(),
            'workflowPlace' => $dimensionContent->getWorkflowPlace(),
            'workflowPublished' => $dimensionContent->getWorkflowPublished()?->format('c'),
        ];

        // Add route if available
        if ($route = $dimensionContent->getRoute()) {
            $data['url'] = $route->getPath();
        }

        // Merge template data
        $templateData = $dimensionContent->getTemplateData();
        if (is_array($templateData)) {
            $data = array_merge($data, $templateData);
        }

        return $data;
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

    protected function getRequestParameter(Request $request, string $key, bool $isRequired = false, $default = null)
    {
        $value = $request->get($key, $default);

        if ($isRequired && null === $value) {
            throw new \InvalidArgumentException(sprintf('Missing required parameter "%s"', $key));
        }

        return $value;
    }

    public function getSecurityContext(): string
    {
        return Event::SECURITY_CONTEXT;
    }
}
