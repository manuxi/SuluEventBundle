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

        // Mock Webspace Manager to return one locale 'en'
        $webspace = new Webspace();
        $localization = new Localization();
        $localization->setLanguage('en');
        $webspace->addLocalization($localization);

        // Pass array to constructor assuming it accepts it, or mock the collection
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
        $event->method('getId')->willReturn(1);

        $domainEvent = $this->createMock(CreatedEvent::class);
        $domainEvent->method('getEntity')->willReturn($event);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getTitle')->willReturn('Test Event');
        $dimensionContent->method('getWorkflowPlace')->willReturn(WorkflowInterface::WORKFLOW_PLACE_DRAFT);

        $this->contentAggregator->method('aggregate')->willReturn($dimensionContent);

        $this->engine->expects($this->once())
            ->method('saveDocument')
            ->with('admin', $this->callback(function ($doc) {
                return 'event-1-en' === $doc['id'] && 'Test Event' === $doc['title'];
            }));

        $this->listener->onCreatedOrModified($domainEvent);
    }

    public function testOnPublished(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn(1);

        $domainEvent = $this->createMock(PublishedEvent::class);
        $domainEvent->method('getEntity')->willReturn($event);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getTitle')->willReturn('Published Event');
        $dimensionContent->method('getWorkflowPlace')->willReturn(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        $this->contentAggregator->method('aggregate')->willReturn($dimensionContent);

        // Expect calling saveDocument twice (admin and website)
        $msg = '';
        $this->engine->expects($this->exactly(2))
            ->method('saveDocument')
            ->willReturnCallback(function (string $index, array $document) use (&$msg) {
                if ('admin' === $index && 'event-1-en' === $document['id']) {
                    $msg .= 'admin_ok';
                } elseif ('website' === $index && 'event-1-en' === $document['id']) {
                    $msg .= 'website_ok';
                }
            });

        $this->listener->onPublished($domainEvent);
    }

    public function testOnRemoved(): void
    {
        $domainEvent = $this->createMock(RemovedEvent::class);
        $domainEvent->method('getResourceId')->willReturn('1');

        $this->engine->expects($this->exactly(2))
            ->method('deleteDocument')
            ->willReturnCallback(function (string $index, string $id) {
                if (!in_array($index, ['admin', 'website'])) {
                    throw new \Exception("Unexpected index: $index");
                }
                if ('event-1-en' !== $id) {
                    throw new \Exception("Unexpected id: $id");
                }
            });

        $this->listener->onRemoved($domainEvent);
    }
}
