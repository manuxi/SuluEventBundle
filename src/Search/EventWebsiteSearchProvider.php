<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Search;

use CmsIg\Seal\Reindex\ReindexConfig;
use CmsIg\Seal\Reindex\ReindexProviderInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

/**
 * Provides ONLY published events for website search.
 */
class EventWebsiteSearchProvider implements ReindexProviderInterface
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly WebspaceManagerInterface $webspaceManager,
    ) {
    }

    public static function getIndex(): string
    {
        return 'website';
    }

    public function total(): ?int
    {
        return $this->eventRepository->countPublished();
    }

    public function provide(ReindexConfig $reindexConfig): \Generator
    {
        $locales = $this->getLocales();

        foreach ($locales as $locale) {
            // Only published events!
            foreach ($this->eventRepository->findPublishedForLocale($locale) as $event) {
                yield $this->createDocument($event);
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

    private function createDocument(Event $event): array
    {
        $content = array_filter([
            $event->getSubtitle(),
            $event->getSummary(),
            $event->getText(),
            $event->getFooter(),
        ]);

        return [
            'id' => 'event-'.$event->getId().'-'.$event->getLocale(),
            'resourceKey' => Event::RESOURCE_KEY,
            'resourceId' => (string) $event->getId(),
            'locale' => $event->getLocale(),
            'webspaces' => [],
            'title' => $event->getTitle() ?? '',
            'url' => $event->getRoutePath() ?? '',
            'content' => $content,
            'mediaId' => $event->getImage()?->getId(),
            'startDate' => $event->getStartDate()?->format('c'),
        ];
    }
}
