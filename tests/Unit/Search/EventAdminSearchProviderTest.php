<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Search;

use CmsIg\Seal\Reindex\ReindexConfig;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Search\EventAdminSearchProvider;
use PHPUnit\Framework\TestCase;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceCollection;
use Sulu\Component\Webspace\Webspace;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;

class EventAdminSearchProviderTest extends TestCase
{
    private EventAdminSearchProvider $provider;
    private EventRepository $eventRepository;
    private WebspaceManagerInterface $webspaceManager;
    private ContentAggregatorInterface $contentAggregator;

    protected function setUp(): void
    {
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $this->contentAggregator = $this->createMock(ContentAggregatorInterface::class);

        // Mock Webspace Manager to return 'en'
        $webspace = new Webspace();
        $localization = new Localization();
        $localization->setLanguage('en');
        $webspace->addLocalization($localization);

        $collection = new WebspaceCollection(['en' => $webspace]);

        $this->webspaceManager->method('getWebspaceCollection')->willReturn($collection);

        $this->provider = new EventAdminSearchProvider(
            $this->eventRepository,
            $this->webspaceManager,
            $this->contentAggregator
        );
    }

    public function testProvide(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn(1);

        $this->eventRepository->method('findAll')->willReturn([$event]);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getTitle')->willReturn('Indexed Event');

        $this->contentAggregator->method('aggregate')->willReturn($dimensionContent);

        $reindexConfig = ReindexConfig::create();

        $generator = $this->provider->provide($reindexConfig);
        $documents = iterator_to_array($generator);

        $this->assertCount(1, $documents);
        $this->assertEquals('event-1-en-draft', $documents[0]['id']);
        $this->assertEquals('Indexed Event', $documents[0]['title']);
    }

    public function testTotal(): void
    {
        $this->eventRepository->method('countAll')->willReturn(10);
        $this->assertEquals(10, $this->provider->total());
    }
}