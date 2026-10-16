<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Controller\Website;

use Manuxi\SuluEventBundle\Controller\Website\FeedController;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FeedControllerTest extends TestCase
{
    private const UUID = '019bf796-423c-7e1f-969c-5c4ece5e9b73';

    private EventRepository&MockObject $repository;
    private ContentAggregatorInterface&MockObject $aggregator;
    private FeedController $controller;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EventRepository::class);
        $this->aggregator = $this->createMock(ContentAggregatorInterface::class);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('https://example.com/events/feed.en.rss');

        $this->controller = new FeedController($this->repository, $urlGenerator, $this->aggregator);
    }

    /**
     * Sulu 3 leaves workflowPlace empty on the live-stage dimension content, only workflowPublished is set.
     */
    public function testRssContainsAnEventPublishedInSulu3WithoutWorkflowPlaceOnTheLiveRow(): void
    {
        $this->repository->method('findAll')->willReturn([new Event(self::UUID)]);
        $this->aggregator->method('aggregate')->willReturn($this->liveContent('Published in Sulu 3', null));

        $response = $this->controller->feedAction('en', 'rss');
        $xml = new \SimpleXMLElement((string) $response->getContent());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(1, $xml->channel->item);
        $this->assertSame('Published in Sulu 3', (string) $xml->channel->item[0]->title);
        $this->assertSame('Thu, 15 Oct 2026 10:00:00 +0000', (string) $xml->channel->item[0]->pubDate);
    }

    public function testAtomContainsAnEventPublishedInSulu3WithoutWorkflowPlaceOnTheLiveRow(): void
    {
        $this->repository->method('findAll')->willReturn([new Event(self::UUID)]);
        $this->aggregator->method('aggregate')->willReturn($this->liveContent('Published in Sulu 3', null));

        $response = $this->controller->feedAction('en', 'atom');
        $xml = new \SimpleXMLElement((string) $response->getContent());

        $this->assertCount(1, $xml->entry);
        $this->assertSame('Published in Sulu 3', (string) $xml->entry[0]->title);
        $this->assertSame('2026-10-15T10:00:00+00:00', (string) $xml->entry[0]->published);
    }

    public function testEventsWithoutLiveRowForTheLocaleAreSkipped(): void
    {
        $published = new Event(self::UUID);
        $draftOnly = new Event('019bf796-423c-7e1f-969c-5c4ece5e9b74');
        $this->repository->method('findAll')->willReturn([$draftOnly, $published]);
        $this->aggregator->method('aggregate')->willReturnCallback(
            function (Event $event) use ($draftOnly): EventDimensionContent {
                if ($event === $draftOnly) {
                    throw new ContentNotFoundException($event, ['locale' => 'en', 'stage' => 'live']);
                }

                return $this->liveContent('Live event', null);
            }
        );

        $xml = new \SimpleXMLElement((string) $this->controller->feedAction('en', 'rss')->getContent());

        $this->assertCount(1, $xml->channel->item);
        $this->assertSame('Live event', (string) $xml->channel->item[0]->title);
    }

    public function testEventsWithoutTitleAreSkipped(): void
    {
        $this->repository->method('findAll')->willReturn([new Event(self::UUID)]);
        $this->aggregator->method('aggregate')->willReturn($this->liveContent(null, null));

        $xml = new \SimpleXMLElement((string) $this->controller->feedAction('en', 'rss')->getContent());

        $this->assertCount(0, $xml->channel->item);
    }

    public function testMigratedLiveRowsWithWorkflowPlacePublishedStillAppear(): void
    {
        $this->repository->method('findAll')->willReturn([new Event(self::UUID)]);
        $this->aggregator->method('aggregate')->willReturn($this->liveContent('Migrated from Sulu 2', 'published'));

        $xml = new \SimpleXMLElement((string) $this->controller->feedAction('en', 'rss')->getContent());

        $this->assertCount(1, $xml->channel->item);
    }

    private function liveContent(?string $title, ?string $workflowPlace): EventDimensionContent
    {
        $content = new EventDimensionContent(new Event(self::UUID));
        $content->setTitle($title);
        $content->setWorkflowPlace($workflowPlace);
        $content->setWorkflowPublished(new \DateTimeImmutable('2026-10-15 10:00:00', new \DateTimeZone('UTC')));

        return $content;
    }
}
