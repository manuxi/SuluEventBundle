<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Search;

use CmsIg\Seal\Reindex\ReindexConfig;
use CmsIg\Seal\Reindex\ReindexProviderInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

/**
 * Provides ALL events (draft + published) for admin search.
 */
class EventAdminSearchProvider implements ReindexProviderInterface
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly WebspaceManagerInterface $webspaceManager,
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
            foreach ($this->eventRepository->findAllForLocale($locale) as $event) {
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
        return [
            'id' => 'event-'.$event->getId().'-'.$event->getLocale(),
            'resourceKey' => Event::RESOURCE_KEY,
            'resourceId' => (string) $event->getId(),
            'locale' => $event->getLocale(),
            'securityContext' => Event::SECURITY_CONTEXT,
            'title' => $event->getTitle() ?? '',
            'mediaId' => $event->getImage()?->getId(),
            'changedAt' => $event->getChanged()?->format('c'),
            'createdAt' => $event->getCreated()?->format('c'),
            'published' => $event->isPublished() ? 1 : 0,
            'startDate' => $event->getStartDate()?->format('c'),
        ];
    }
}
