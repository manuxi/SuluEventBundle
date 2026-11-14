<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

/**
 * The settings of the import that are not about a single source (sulu_event.import).
 */
final class ImportSettings
{
    /**
     * @param string|null               $user                     user name of the Sulu user who is creator and changer of imported events
     * @param bool                      $autoUpdatePublishedDates a date or status change may go live without an editor publishing it
     * @param string                    $urlPrefix                path the urls of imported events start with ("/events")
     * @param string|null               $webspace                 main webspace of imported events, null = the default one
     * @param string                    $defaultType              event type for candidates without one
     * @param array<string, int|string> $categoryImages           category key => id or file name of an image of our own, for events that have none
     * @param string[]                  $requiredLocales          locales an imported event should have a text in (for the list of events that need work)
     * @param int|string|null           $locationImage            id or file name of the image a place gets that the import creates (and that has none)
     * @param array<string, int|string> $seriesImages             series key => id or file name of a photo of that series, for its events (better than the placeholder of the topic)
     * @param string[]                  $featuredSources          keys of sources whose events get the tag of the highlights (sulu_event.featured_tag)
     * @param string[]                  $placeholderCollections  keys of system collections whose images are placeholders (generated cards): a real photo replaces them
     */
    public function __construct(
        public readonly bool $enabled = false,
        public readonly ?string $user = null,
        public readonly bool $autoUpdatePublishedDates = false,
        public readonly string $urlPrefix = '/events',
        public readonly ?string $webspace = null,
        public readonly string $defaultType = 'default',
        public readonly string $template = 'event',
        public readonly array $categoryImages = [],
        public readonly array $requiredLocales = [],
        public readonly int|string|null $locationImage = null,
        public readonly array $seriesImages = [],
        public readonly array $featuredSources = [],
        public readonly array $placeholderCollections = [],
    ) {
    }
}
