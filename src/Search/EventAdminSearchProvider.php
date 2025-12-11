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

class EventAdminSearchProvider implements ReindexProviderInterface
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public static function getIndex(): string
    {
        return 'admin';
    }

    public function total(): ?int
    {
        return $this->eventRepository->countAll();
    }

    public function provide(ReindexConfig $reindexConfig): \Generator
    {
        $locales = $this->getLocales();

        foreach ($locales as $locale) {
            $events = $this->eventRepository->findAll();

            foreach ($events as $event) {
                /** @var EventDimensionContent $dimensionContent */
                $dimensionContent = $this->contentAggregator->aggregate(
                    $event,
                    [
                        'locale' => $locale,
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'version' => DimensionContentInterface::CURRENT_VERSION,
                    ]
                );

                // Skip if no content for this locale
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
            'id' => 'event-' . $event->getId() . '-' . $locale . '-draft',
            'resourceKey' => Event::RESOURCE_KEY,
            'resourceId' => (string) $event->getId(),
            'locale' => $locale,
            'webspaces' => [],
            'title' => $dimensionContent->getTitle() ?? '',
            'url' => $dimensionContent->getRoute()?->getSlug() ?? '',
            'content' => implode(' ', $content),
            'type' => $dimensionContent->getType(),
            'startDate' => $dimensionContent->getStartDate()?->format('c'),
            'endDate' => $dimensionContent->getEndDate()?->format('c'),
            'location' => $locationName,
        ];
    }
}