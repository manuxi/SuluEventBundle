<?php
declare(strict_types=1);
namespace Manuxi\SuluEventBundle\Search;
use CmsIg\Seal\Reindex\ReindexConfig;
use CmsIg\Seal\Reindex\ReindexProviderInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
class EventWebsiteSearchProvider implements ReindexProviderInterface
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }
    public static function getIndex(): string
    {
        return 'website';
    }
    public function total(): ?int
    {
        $locales = $this->getLocales();
        $total = 0;
        foreach ($locales as $locale) {
            $total += $this->eventRepository->countPublished($locale);
        }
        return $total;
    }
    public function provide(ReindexConfig $reindexConfig): \Generator
    {
        $locales = $this->getLocales();
        foreach ($locales as $locale) {
            $events = $this->eventRepository->findAllByLocale($locale, DimensionContentInterface::STAGE_LIVE);
            foreach ($events as $event) {
                $hasLiveContent = false;
                foreach ($event->getDimensionContents() as $content) {
                    if ($content->getLocale() === $locale && DimensionContentInterface::STAGE_LIVE === $content->getStage()) {
                        $hasLiveContent = true;
                        break;
                    }
                }
                if (!$hasLiveContent) {
                    continue;
                }
                /** @var EventDimensionContent $dimensionContent */
                $dimensionContent = $this->contentAggregator->aggregate(
                    $event,
                    [
                        'locale' => $locale,
                        'stage' => DimensionContentInterface::STAGE_LIVE,
                        'version' => DimensionContentInterface::CURRENT_VERSION,
                    ]
                );
                if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED !== $dimensionContent->getWorkflowPlace()) {
                    continue;
                }
                if (!$dimensionContent->getTitle()) {
                    continue;
                }
                yield $this->createDocument($event, $dimensionContent, $locale);
            }
        }
    }
    private function getLocales(): array
    {
        $locales = [];
        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            foreach ($webspace->getAllLocalizations() as $localization) {
                $locales[$localization->getLocale()] = true;
            }
        }
        return array_keys($locales);
    }
    private function createDocument(Event $event, EventDimensionContent $dimensionContent, string $locale): array
    {
        $content = array_filter([
            $dimensionContent->getSubtitle(),
            $dimensionContent->getSummary(),
            $dimensionContent->getText(),
            $dimensionContent->getFooter(),
        ]);
        // All fields (including unlocalized) are now in the merged dimensionContent
        $location = $dimensionContent->getLocation();
        $locationName = $location?->getName();
        return [
            'id' => 'event-' . $event->getId() . '-' . $locale,
            'resourceKey' => Event::RESOURCE_KEY,
            'resourceId' => (string) $event->getId(),
            'locale' => $locale,
            'webspaces' => [],
            'title' => $dimensionContent->getTitle() ?? '',
            'url' => $dimensionContent->getRoute()?->getSlug() ?? '',
            'content' => array_values($content),
            'type' => $dimensionContent->getType(),
            'startDate' => $dimensionContent->getStartDate()?->format('c'),
            'endDate' => $dimensionContent->getEndDate()?->format('c'),
            'location' => $locationName,
            'mediaId' => $dimensionContent->getImage()?->getId(),
        ];
    }
}