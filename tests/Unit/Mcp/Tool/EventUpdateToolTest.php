<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Mcp\Tool;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Mcp\Tool\EventUpdateTool;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

class EventUpdateToolTest extends TestCase
{
    private EventRepository&MockObject $eventRepository;
    private ContentManagerInterface&MockObject $contentManager;
    private ContentWorkflowInterface&MockObject $contentWorkflow;
    private EntityManagerInterface&MockObject $entityManager;
    private EventUpdateTool $tool;
    private Event $event;

    protected function setUp(): void
    {
        $this->event = new Event();

        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->eventRepository->method('findByUuid')->willReturn($this->event);

        $this->contentManager = $this->createMock(ContentManagerInterface::class);
        $this->contentWorkflow = $this->createMock(ContentWorkflowInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->tool = new EventUpdateTool(
            $this->eventRepository,
            $this->entityManager,
            $this->contentManager,
            $this->contentWorkflow,
            $this->createMock(DomainEventCollectorInterface::class),
        );
    }

    public function testOnlyTheGivenFieldsAndTheTemplateKeyArePersisted(): void
    {
        $current = $this->createDimensionContent('event_detailed', WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $this->contentManager->method('resolve')->willReturn($current);

        // the form representation (normalize) carries the full media object of the image and must not be written back
        $this->contentManager->expects($this->never())->method('normalize');

        $this->contentManager->expects($this->once())
            ->method('persist')
            ->with(
                $this->identicalTo($this->event),
                ['summary' => 'New summary', 'template' => 'event_detailed'],
                ['locale' => 'de', 'stage' => 'draft'],
            )
            ->willReturn($current);

        $result = $this->tool->updateEvent($this->event->getUuid(), 'de', null, ['summary' => 'New summary']);

        $this->assertSame(['success' => true, 'uuid' => $this->event->getUuid(), 'locale' => 'de'], $result);
    }

    public function testTitleIsPersistedAndAFieldInContentWins(): void
    {
        $current = $this->createDimensionContent('event', WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $this->contentManager->method('resolve')->willReturn($current);

        $this->contentManager->expects($this->once())
            ->method('persist')
            ->with($this->anything(), ['title' => 'From content', 'template' => 'event'], $this->anything())
            ->willReturn($current);

        $this->tool->updateEvent($this->event->getUuid(), 'de', 'From parameter', ['title' => 'From content']);
    }

    public function testTitleParameterIsUsedWithoutContent(): void
    {
        $current = $this->createDimensionContent('event', WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $this->contentManager->method('resolve')->willReturn($current);

        $this->contentManager->expects($this->once())
            ->method('persist')
            ->with($this->anything(), ['title' => 'New title', 'template' => 'event'], $this->anything())
            ->willReturn($current);

        $this->tool->updateEvent($this->event->getUuid(), 'de', 'New title');
    }

    public function testPublishedEventBecomesADraftAgain(): void
    {
        $current = $this->createDimensionContent('event', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        $this->contentManager->method('resolve')->willReturn($current);
        $this->contentManager->method('persist')->willReturn($current);

        $this->contentWorkflow->expects($this->once())
            ->method('apply')
            ->with($this->identicalTo($this->event), ['locale' => 'de'], WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT);

        $this->tool->updateEvent($this->event->getUuid(), 'de', null, ['summary' => 'x']);
    }

    public function testUnknownEventReturnsAnError(): void
    {
        $repository = $this->createMock(EventRepository::class);
        $repository->method('findByUuid')->willReturn(null);

        $tool = new EventUpdateTool(
            $repository,
            $this->entityManager,
            $this->contentManager,
            $this->contentWorkflow,
            $this->createMock(DomainEventCollectorInterface::class),
        );

        $this->contentManager->expects($this->never())->method('persist');

        $result = $tool->updateEvent('missing', 'de', null, ['summary' => 'x']);

        $this->assertSame(['error' => 'Event "missing" not found.'], $result);
    }

    private function createDimensionContent(string $templateKey, string $workflowPlace): EventDimensionContent
    {
        $content = new EventDimensionContent($this->event);
        $content->setLocale('de');
        $content->setTemplateKey($templateKey);
        $content->setWorkflowPlace($workflowPlace);

        return $content;
    }
}
