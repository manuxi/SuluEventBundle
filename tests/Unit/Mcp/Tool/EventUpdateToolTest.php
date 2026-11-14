<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Mcp\Tool;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Import\Filter\CandidateFilter;
use Manuxi\SuluEventBundle\Import\Resolver\CategoryResolver;
use Manuxi\SuluEventBundle\Import\Resolver\TagResolver;
use Manuxi\SuluEventBundle\Mcp\Tool\EventUpdateTool;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\FeaturedTag;
use Manuxi\SuluEventBundle\Tests\Integration\Stub\StubTag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

class EventUpdateToolTest extends TestCase
{
    private EventRepository&MockObject $eventRepository;
    private ContentManagerInterface&MockObject $contentManager;
    private ContentWorkflowInterface&MockObject $contentWorkflow;
    private EntityManagerInterface&MockObject $entityManager;
    private CategoryResolver&MockObject $categoryResolver;
    private TagResolver&MockObject $tagResolver;
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
        $this->categoryResolver = $this->createMock(CategoryResolver::class);
        $this->tagResolver = $this->createMock(TagResolver::class);

        $this->tool = new EventUpdateTool(
            $this->eventRepository,
            $this->entityManager,
            $this->contentManager,
            $this->contentWorkflow,
            $this->createMock(DomainEventCollectorInterface::class),
            $this->categoryResolver,
            $this->tagResolver,
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

    public function testCategoryKeysAndTagNamesBecomeIds(): void
    {
        $current = $this->createDimensionContent('event', WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $this->contentManager->method('resolve')->willReturn($current);

        $category = $this->createMock(CategoryInterface::class);
        $category->method('getId')->willReturn(22);
        $this->categoryResolver->method('resolve')->with(['web-php', 'nope'])->willReturn([[$category], ['nope']]);
        $tag = $this->createMock(TagInterface::class);
        $tag->method('getId')->willReturn(7);
        $this->tagResolver->method('resolve')->with(['Symfony'])->willReturn([$tag]);
        // a new tag needs its id, then the update itself
        $this->entityManager->expects($this->exactly(2))->method('flush');

        $this->contentManager->expects($this->once())
            ->method('persist')
            ->with($this->anything(), ['summary' => 'S', 'excerptCategories' => [22], 'excerptTags' => [7], 'template' => 'event'], $this->anything())
            ->willReturn($current);

        $result = $this->tool->updateEvent($this->event->getUuid(), 'de', null, ['summary' => 'S', 'categories' => ['web-php', 'nope'], 'tags' => ['Symfony']]);

        $this->assertTrue($result['success'] ?? false, json_encode($result));
        $this->assertSame(['Unknown category keys: nope'], $result['warnings']);
    }

    public function testOnlyAllowedTagsAreResolvedAndTheOthersAreNamedInTheWarnings(): void
    {
        $current = $this->createDimensionContent('event', WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $this->contentManager->method('resolve')->willReturn($current);
        $this->contentManager->method('persist')->willReturn($current);

        $tag = $this->createMock(TagInterface::class);
        $tag->method('getId')->willReturn(7);
        $this->tagResolver->expects($this->once())->method('resolve')->with(['Symfony'])->willReturn([$tag]);

        $tool = new EventUpdateTool(
            $this->eventRepository,
            $this->entityManager,
            $this->contentManager,
            $this->contentWorkflow,
            $this->createMock(DomainEventCollectorInterface::class),
            $this->categoryResolver,
            $this->tagResolver,
            new CandidateFilter(['tags_allowed' => ['symfony', 'php']]),
        );

        $result = $tool->updateEvent($this->event->getUuid(), 'de', null, ['tags' => ['Symfony', 'Whatever']]);

        $this->assertTrue($result['success'] ?? false, json_encode($result));
        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('Whatever', $result['warnings'][0]);
    }

    public function testTheTagOfTheHighlightsStaysWhenTagsAreReplaced(): void
    {
        $current = $this->createDimensionContent('event', WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $current->setExcerptTags([new StubTag('Symfony'), new StubTag('Highlight')]);
        $this->contentManager->method('resolve')->willReturn($current);
        $this->contentManager->method('persist')->willReturn($current);

        $tag = $this->createMock(TagInterface::class);
        $tag->method('getId')->willReturn(7);
        // the list of allowed tags does not name the highlight, it passes anyway
        $this->tagResolver->expects($this->once())->method('resolve')->with(['php', 'Highlight'])->willReturn([$tag]);

        $result = $this->createToolWithFeaturedTag('Highlight', new CandidateFilter(['tags_allowed' => ['php']]))
            ->updateEvent($this->event->getUuid(), 'de', null, ['tags' => ['php']]);

        $this->assertTrue($result['success'] ?? false, json_encode($result));
        $this->assertArrayNotHasKey('warnings', $result);
    }

    public function testFeaturedFalseTakesTheTagOfTheHighlightsAwayAndKeepsTheOthers(): void
    {
        $current = $this->createDimensionContent('event', WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $current->setExcerptTags([new StubTag('Symfony'), new StubTag('Highlight')]);
        $this->contentManager->method('resolve')->willReturn($current);
        $this->contentManager->method('persist')->willReturn($current);
        $this->tagResolver->expects($this->once())->method('resolve')->with(['Symfony'])->willReturn([]);

        $result = $this->createToolWithFeaturedTag('Highlight')->updateEvent($this->event->getUuid(), 'de', null, ['featured' => false]);

        $this->assertTrue($result['success'] ?? false, json_encode($result));
    }

    public function testFeaturedTrueAddsTheTagToTheCurrentOnes(): void
    {
        $current = $this->createDimensionContent('event', WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $current->setExcerptTags([new StubTag('Symfony')]);
        $this->contentManager->method('resolve')->willReturn($current);
        $this->contentManager->method('persist')->willReturn($current);
        $this->tagResolver->expects($this->once())->method('resolve')->with(['Symfony', 'Highlight'])->willReturn([]);

        $this->createToolWithFeaturedTag('Highlight')->updateEvent($this->event->getUuid(), 'de', null, ['featured' => true]);
    }

    public function testFeaturedIsIgnoredWithAWarningWithoutTheFeaturedTag(): void
    {
        $current = $this->createDimensionContent('event', WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $this->contentManager->method('resolve')->willReturn($current);
        $this->contentManager->expects($this->once())
            ->method('persist')
            ->with($this->anything(), ['summary' => 'S', 'template' => 'event'], $this->anything())
            ->willReturn($current);
        $this->tagResolver->expects($this->never())->method('resolve');

        $result = $this->tool->updateEvent($this->event->getUuid(), 'de', null, ['summary' => 'S', 'featured' => true]);

        $this->assertStringContainsString('sulu_event.featured_tag', $result['warnings'][0]);
    }

    public function testWithoutCategoriesAndTagsNothingIsResolved(): void
    {
        $current = $this->createDimensionContent('event', WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $this->contentManager->method('resolve')->willReturn($current);
        $this->contentManager->method('persist')->willReturn($current);
        $this->categoryResolver->expects($this->never())->method('resolve');
        $this->tagResolver->expects($this->never())->method('resolve');

        $result = $this->tool->updateEvent($this->event->getUuid(), 'de', null, ['summary' => 'S']);

        $this->assertArrayNotHasKey('warnings', $result);
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
            $this->categoryResolver,
            $this->tagResolver,
        );

        $this->contentManager->expects($this->never())->method('persist');

        $result = $tool->updateEvent('missing', 'de', null, ['summary' => 'x']);

        $this->assertSame(['error' => 'Event "missing" not found.'], $result);
    }

    private function createToolWithFeaturedTag(string $name, ?CandidateFilter $filter = null): EventUpdateTool
    {
        return new EventUpdateTool(
            $this->eventRepository,
            $this->entityManager,
            $this->contentManager,
            $this->contentWorkflow,
            $this->createMock(DomainEventCollectorInterface::class),
            $this->categoryResolver,
            $this->tagResolver,
            $filter,
            new FeaturedTag($name),
        );
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
