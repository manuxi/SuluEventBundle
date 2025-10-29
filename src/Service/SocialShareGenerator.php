<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\Event;

class SocialShareGenerator
{
    public function generateShareLinks(Event $event, string $locale): array
    {
        $settings = $event->getSocialSettings();
        $url = urlencode($this->getEventUrl($event));
        $title = urlencode($event->getTitle());

        // Use custom share text or fallback to title
        $shareText = $settings->getCustomShareText()
            ? urlencode($settings->getCustomShareText())
            : $title;

        $allLinks = [
            'facebook' => sprintf('https://www.facebook.com/sharer/sharer.php?u=%s', $url),
            'twitter' => sprintf('https://twitter.com/intent/tweet?url=%s&text=%s', $url, $shareText),
            'linkedin' => sprintf('https://www.linkedin.com/sharing/share-offsite/?url=%s', $url),
            'whatsapp' => sprintf('https://wa.me/?text=%s%%20%s', $shareText, $url),
            'email' => sprintf('mailto:?subject=%s&body=%s', $title, $url),
        ];

        // Filter by enabled platforms
        $enabledPlatforms = $settings->getSocialPlatforms() ?? [];

        return array_filter(
            $allLinks,
            fn ($key) => in_array($key, $enabledPlatforms, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Generate Open Graph meta tags for social media.
     */
    public function generateOpenGraphTags(Event $event): array
    {
        $tags = [
            'og:type' => 'event',
            'og:title' => $event->getTitle(),
            'og:url' => $this->getEventUrl($event),
            'event:start_time' => $event->getStartDate()->format('c'),
        ];

        // Add optional properties
        if ($event->getSummary()) {
            $tags['og:description'] = $event->getSummary();
        }

        if ($event->getImage()) {
            $tags['og:image'] = $event->getImage()->getUrl();
        }

        if ($event->getEndDate()) {
            $tags['event:end_time'] = $event->getEndDate()->format('c');
        }

        if ($event->getLocation()) {
            $tags['event:location'] = $event->getLocation()->getName();
        }

        return $tags;
    }

    /**
     * Generate Twitter Card meta tags.
     */
    public function generateTwitterCardTags(Event $event): array
    {
        $tags = [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $event->getTitle(),
            'twitter:url' => $this->getEventUrl($event),
        ];

        if ($event->getSummary()) {
            $tags['twitter:description'] = $event->getSummary();
        }

        if ($event->getImage()) {
            $tags['twitter:image'] = $event->getImage()->getUrl();
        }

        return $tags;
    }

    /**
     * Get absolute URL for event.
     */
    private function getEventUrl(Event $event): string
    {
        // Use the event's route path as absolute URL
        // In production, this should generate a full absolute URL
        return $event->getRoutePath();
    }
}
