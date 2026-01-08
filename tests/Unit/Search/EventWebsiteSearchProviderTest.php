<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Search;

use CmsIg\Seal\Reindex\ReindexConfig;
use Doctrine\Common\Collections\ArrayCollection;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Search\EventWebsiteSearchProvider;
use PHPUnit\Framework\TestCase;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceCollection;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Webspace;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class EventWebsiteSearchProviderTest extends TestCase
{
    private EventWebsiteSearchProvider $provider;
    private EventRepository $eventRepository;
    private WebspaceManagerInterface $webspaceManager;
    private ContentAggregatorInterface $contentAggregator;

    protected function setUp(): void
    {
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $this->contentAggregator = $this->createMock(ContentAggregatorInterface::class);

        $webspace = new Webspace();
        $localization = new Localization();
        $localization->setLanguage('en');
        $webspace->addLocalization($localization);

        $collection = new WebspaceCollection(['en' => $webspace]);

        $this->webspaceManager->method('getWebspaceCollection')->willReturn($collection);

        $this->provider = new EventWebsiteSearchProvider(
            $this->eventRepository,
            $this->webspaceManager,
            $this->contentAggregator
        );
    }

    public function testProvideSkipsEventsWithoutLiveContent(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn(1);

        $draftContent = $this->createMock(EventDimensionContent::class);
        $draftContent->method('getStage')->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $draftContent->method('getLocale')->willReturn('en');

        $event->method('getDimensionContents')->willReturn(new ArrayCollection([$draftContent]));

        $this->eventRepository->method('findAllByLocale')
            ->with('en', DimensionContentInterface::STAGE_LIVE)
            ->willReturn([$event]);

        $this->contentAggregator->expects($this->never())->method('aggregate');

        $reindexConfig = ReindexConfig::create();
        $generator = $this->provider->provide($reindexConfig);

        $documents = iterator_to_array($generator);

        $this->assertCount(0, $documents);
    }
}
