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

    public function generateShareLinks(Event $event, string $locale): array
    {
        /** @var EventDimensionContent $dimensionContent */
        $dimensionContent = $this->contentAggregator->aggregate($event, [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_LIVE,
        ]);

        // Get socialSettings from Event (not DimensionContent!)
        $settings = $event->getSocialSettings();
        if (!$settings || !$settings->isEnableSharing()) {
            return [];
        }

        $url = urlencode($this->getEventUrl($dimensionContent));
        $title = urlencode($dimensionContent->getTitle() ?? '');

        $customText = $settings->getCustomShareText()
            ? urlencode($settings->getCustomShareText())
            : $title;

        $allLinks = [
            'facebook' => sprintf('https://www.facebook.com/sharer/sharer.php?u=%s&quote=%s', $url, $customText),
            'twitter' => sprintf('https://twitter.com/intent/tweet?url=%s&text=%s', $url, $customText),
            'linkedin' => sprintf('https://www.linkedin.com/sharing/share-offsite/?url=%s&summary=%s', $url, $customText),
            'whatsapp' => sprintf('https://wa.me/?text=%s%%20%s', $customText, $url),
            'instagram' => $settings->getInstagramUrl(),
            'email' => sprintf('mailto:?subject=%s&body=%s', $title, $url),
        ];

        // Filter by enabled platforms
        $enabledPlatforms = $settings->getPlatforms() ?? [];
        if (!empty($enabledPlatforms)) {
            $allLinks = array_intersect_key($allLinks, array_flip($enabledPlatforms));
        }

        return $allLinks;
    }

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
        ];

        if ($startDate = $dimensionContent->getStartDate()) {
            $tags['event:start_time'] = $startDate->format('c');
        }

        if ($dimensionContent->getSummary()) {
            $tags['og:description'] = $dimensionContent->getSummary();
        }

        if ($image = $dimensionContent->getImage()) {
            $media = $this->mediaManager->getById($image->getId(), $locale);
            $tags['og:image'] = $media->getUrl();
        }

        if ($endDate = $dimensionContent->getEndDate()) {
            $tags['event:end_time'] = $endDate->format('c');
        }

        if ($location = $dimensionContent->getLocation()) {
            $tags['event:location'] = $location->getName();
        }

        return $tags;
    }

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

        // Add Twitter handle if configured
        $socialSettings = $event->getSocialSettings();
        if ($socialSettings && $socialSettings->getTwitterHandle()) {
            $tags['twitter:site'] = $socialSettings->getTwitterHandle();
        }

        return $tags;
    }

    private function getEventUrl(EventDimensionContent $dimensionContent): string
    {
        $route = $dimensionContent->getRoute();
        return $route ? $route->getSlug() : '';
    }
}