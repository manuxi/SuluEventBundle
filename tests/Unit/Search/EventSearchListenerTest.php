<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Search;

use CmsIg\Seal\EngineInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\CreatedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\PublishedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\RemovedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Search\EventSearchListener;
use PHPUnit\Framework\TestCase;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceCollection;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Webspace;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

class EventSearchListenerTest extends TestCase
{
    private EventSearchListener $listener;
    private EngineInterface $engine;
    private WebspaceManagerInterface $webspaceManager;
    private ContentAggregatorInterface $contentAggregator;

    protected function setUp(): void
    {
        $this->engine = $this->createMock(EngineInterface::class);
        $this->webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $this->contentAggregator = $this->createMock(ContentAggregatorInterface::class);

        $webspace = new Webspace();
        $localization = new Localization();
        $localization->setLanguage('en');
        $webspace->addLocalization($localization);

        $collection = new WebspaceCollection(['en' => $webspace]);

        $this->webspaceManager->method('getWebspaceCollection')->willReturn($collection);

        $this->listener = new EventSearchListener(
            $this->engine,
            $this->webspaceManager,
            $this->contentAggregator
        );
    }

    public function testOnCreatedOrModified(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn('019bf796-423c-7e1f-969c-5c4ece5e9b73');

        $domainEvent = $this->createMock(CreatedEvent::class);
        $domainEvent->method('getEntity')->willReturn($event);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getTitle')->willReturn('Test Event');
        $dimensionContent->method('getWorkflowPlace')->willReturn(WorkflowInterface::WORKFLOW_PLACE_DRAFT);

        $this->contentAggregator->method('aggregate')->willReturn($dimensionContent);

        $this->engine->expects($this->once())
            ->method('saveDocument')
            ->with('admin', $this->callback(function ($doc) {
                return 'event-019bf796-423c-7e1f-969c-5c4ece5e9b73-en' === $doc['id'] && 'Test Event' === $doc['title'];
            }));

        $this->listener->onCreatedOrModified($domainEvent);
    }

    public function testOnPublished(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn('019bf796-423c-7e1f-969c-5c4ece5e9b73');

        $domainEvent = $this->createMock(PublishedEvent::class);
        $domainEvent->method('getEntity')->willReturn($event);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getTitle')->willReturn('Published Event');
        $dimensionContent->method('getWorkflowPlace')->willReturn(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        $this->contentAggregator->method('aggregate')->willReturn($dimensionContent);

        $msg = '';
        $this->engine->expects($this->exactly(2))
            ->method('saveDocument')
            ->willReturnCallback(function (string $index, array $document) use (&$msg) {
                if ('admin' === $index && 'event-019bf796-423c-7e1f-969c-5c4ece5e9b73-en' === $document['id']) {
                    $msg .= 'admin_ok';
                } elseif ('website' === $index && 'event-019bf796-423c-7e1f-969c-5c4ece5e9b73-en' === $document['id']) {
                    $msg .= 'website_ok';
                }
            });

        $this->listener->onPublished($domainEvent);
    }

    /**
     * Sulu 3 leaves workflowPlace empty on the live-stage dimension content: the publish event itself is the signal.
     */
    public function testOnPublishedMarksTheAdminDocumentAsPublishedEvenWithoutWorkflowPlaceOnTheLiveRow(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn('019bf796-423c-7e1f-969c-5c4ece5e9b73');

        $domainEvent = $this->createMock(PublishedEvent::class);
        $domainEvent->method('getEntity')->willReturn($event);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getTitle')->willReturn('Published in Sulu 3');
        $dimensionContent->method('getWorkflowPlace')->willReturn(null);

        $this->contentAggregator->method('aggregate')->willReturn($dimensionContent);

        $adminDocument = null;
        $this->engine->method('saveDocument')
            ->willReturnCallback(function (string $index, array $document) use (&$adminDocument) {
                if ('admin' === $index) {
                    $adminDocument = $document;
                }

                return null;
            });

        $this->listener->onPublished($domainEvent);

        $this->assertNotNull($adminDocument);
        $this->assertSame('1', $adminDocument['published']);
        $this->assertSame(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED, $adminDocument['workflowPlace']);
    }

    public function testOnCreatedOrModifiedKeepsTheWorkflowPlaceOfTheDraft(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn('019bf796-423c-7e1f-969c-5c4ece5e9b73');

        $domainEvent = $this->createMock(CreatedEvent::class);
        $domainEvent->method('getEntity')->willReturn($event);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getTitle')->willReturn('Draft');
        $dimensionContent->method('getWorkflowPlace')->willReturn(WorkflowInterface::WORKFLOW_PLACE_DRAFT);

        $this->contentAggregator->method('aggregate')->willReturn($dimensionContent);

        $this->engine->expects($this->once())
            ->method('saveDocument')
            ->with('admin', $this->callback(static fn (array $doc): bool => '0' === $doc['published'] && 'draft' === $doc['workflowPlace']));

        $this->listener->onCreatedOrModified($domainEvent);
    }

    public function testOnRemoved(): void
    {
        $domainEvent = $this->createMock(RemovedEvent::class);
        $domainEvent->method('getResourceId')->willReturn('019bf796-423c-7e1f-969c-5c4ece5e9b73');

        $this->engine->expects($this->exactly(2))
            ->method('deleteDocument')
            ->willReturnCallback(function (string $index, string $id) {
                if (!in_array($index, ['admin', 'website'])) {
                    throw new \Exception("Unexpected index: $index");
                }
                if ('event-019bf796-423c-7e1f-969c-5c4ece5e9b73-en' !== $id) {
                    throw new \Exception("Unexpected id: $id");
                }
            });

        $this->listener->onRemoved($domainEvent);
    }
}