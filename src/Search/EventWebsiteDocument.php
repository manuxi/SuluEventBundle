<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Search;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;

/**
 * Builds the document of an event for the "website" search index (the schema comes from Sulu, see
 * sulu/search config/schemas/website.php). Only the fields of that schema are stored; anything else is dropped by the
 * search adapter. Everything a result list needs beyond title, text and image therefore goes into "metadata.preview":
 *
 *   subtitle, description (plain text, shortened), startsAt, endsAt (ISO 8601), place ("Name, City"), type (event type key)
 *
 * The indexer of the provider (full reindex) and the listener (publish) both use this class, so a published event looks
 * the same as a reindexed one.
 */
final class EventWebsiteDocument
{
    private const DESCRIPTION_LENGTH = 500;

    /**
     * @param list<string> $webspaces
     *
     * @return array<string, mixed>
     */
    public static function create(Event $event, EventDimensionContent $dimensionContent, string $locale, array $webspaces): array
    {
        $subtitle = self::plain($dimensionContent->getSubtitle());
        $summary = self::plain($dimensionContent->getSummary());
        $text = self::plain($dimensionContent->getText());
        $footer = self::plain($dimensionContent->getFooter());

        $content = array_values(array_unique(array_filter([$subtitle, $summary, $text, $footer], static fn (string $part): bool => '' !== $part)));

        $location = $dimensionContent->getLocation();
        $place = implode(', ', array_filter([$location?->getName(), $location?->getCity()]));

        $preview = array_filter([
            'subtitle' => $subtitle,
            'description' => self::shorten('' !== $summary ? $summary : $text),
            'startsAt' => $dimensionContent->getStartDate()?->format('c'),
            'endsAt' => $dimensionContent->getEndDate()?->format('c'),
            'place' => $place,
            'type' => $dimensionContent->getType(),
        ], static fn (mixed $value): bool => null !== $value && '' !== $value);

        return [
            'id' => 'event-' . $event->getId() . '-' . $locale,
            'resourceKey' => Event::RESOURCE_KEY,
            'resourceId' => (string) $event->getId(),
            'locale' => $locale,
            'webspaces' => $webspaces,
            'title' => $dimensionContent->getTitle() ?? '',
            'url' => $dimensionContent->getRoute()?->getSlug() ?? '',
            'content' => $content,
            'mediaId' => $dimensionContent->getImage()?->getId(),
            'metadata' => [] === $preview ? [] : ['preview' => $preview],
        ];
    }

    private static function plain(?string $html): string
    {
        if (null === $html) {
            return '';
        }

        // block ends become spaces, so that "<p>a</p><p>b</p>" does not read "ab"
        $text = (string) preg_replace('~</?(?:p|div|br|li|ul|ol|h[1-6]|blockquote|tr|td)\b[^>]*>~i', ' ', $html);
        $text = html_entity_decode(strip_tags($text), \ENT_QUOTES | \ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    private static function shorten(string $text): string
    {
        if (mb_strlen($text) <= self::DESCRIPTION_LENGTH) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, self::DESCRIPTION_LENGTH), " ,;:.-") . '…';
    }
}
