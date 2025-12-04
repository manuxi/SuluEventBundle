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

/**
 * Provides ALL events (draft + published) for admin search.
 */
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
            $events = $this->eventRepository->findBy([]);

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
        return [
            'id' => 'event-'.$event->getId().'-'.$locale,
            'resourceKey' => Event::RESOURCE_KEY,
            'resourceId' => (string) $event->getId(),
            'locale' => $locale,
            'securityContext' => Event::SECURITY_CONTEXT,
            'title' => $dimensionContent->getTitle() ?? '',
            'mediaId' => $dimensionContent->getImage()?->getId(),
            'changedAt' => $dimensionContent->getChanged()?->format('c'),
            'createdAt' => $dimensionContent->getCreated()?->format('c'),
            'published' => (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace()) ? 1 : 0,
            'startDate' => $event->getStartDate()?->format('c'),
        ];
    }
}
