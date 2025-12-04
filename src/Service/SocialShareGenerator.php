<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class SocialShareGenerator
{
    public function __construct(
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly MediaManagerInterface $mediaManager,
    ) {
    }

    /**
     * Generate share links for an event
     */
    public function generateShareLinks(Event $event, string $locale): array
    {
        /** @var EventDimensionContent $dimensionContent */
        $dimensionContent = $this->contentAggregator->aggregate($event, [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_LIVE,
        ]);

        $settings = $event->getSocialSettings();
        if (!$settings || !$settings->getEnableSharing()) {
            return [];
        }

        $url = urlencode($this->getEventUrl($dimensionContent));
        $title = urlencode($dimensionContent->getTitle() ?? '');

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
        $enabledPlatforms = $settings->getPlatforms() ?? [];

        return array_filter(
            $allLinks,
            fn ($key) => in_array($key, $enabledPlatforms, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Generate Open Graph meta tags for social media.
     */
    public function generateOpenGraphTags(Event $event, string $locale): array
    {
        /** @var EventDimensionContent $dimensionContent */
        $dimensionContent = $this->contentAggregator->aggregate($event, [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_LIVE,
        ]);

        $tags = [
            'og:type' => 'event',
            'og:title' => $dimensionContent->getTitle() ?? '',
            'og:url' => $this->getEventUrl($dimensionContent),
            'event:start_time' => $event->getStartDate()->format('c'),
        ];

        // Add optional properties
        if ($dimensionContent->getSummary()) {
            $tags['og:description'] = $dimensionContent->getSummary();
        }

        if ($image = $dimensionContent->getImage()) {
            $media = $this->mediaManager->getById($image->getId(), $locale);
            $tags['og:image'] = $media->getUrl();
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
    public function generateTwitterCardTags(Event $event, string $locale): array
    {
        /** @var EventDimensionContent $dimensionContent */
        $dimensionContent = $this->contentAggregator->aggregate($event, [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_LIVE,
        ]);

        $tags = [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $dimensionContent->getTitle() ?? '',
            'twitter:url' => $this->getEventUrl($dimensionContent),
        ];

        if ($dimensionContent->getSummary()) {
            $tags['twitter:description'] = $dimensionContent->getSummary();
        }

        if ($image = $dimensionContent->getImage()) {
            $media = $this->mediaManager->getById($image->getId(), $locale);
            $tags['twitter:image'] = $media->getUrl();
        }

        return $tags;
    }

    /**
     * Get absolute URL for event.
     */
    private function getEventUrl(EventDimensionContent $dimensionContent): string
    {
        $route = $dimensionContent->getRoute();
        return $route ? $route->getSlug() : '';
    }
}