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

        // Get unlocalized dimension content for socialSettings
        $unlocalizedDimensionContent = $this->getUnlocalizedDimensionContent($event);
        if (!$unlocalizedDimensionContent) {
            return [];
        }

        $settings = $unlocalizedDimensionContent->getSocialSettings();
        if (!$settings) {
            return [];
        }

        $url = urlencode($this->getEventUrl($dimensionContent));
        $title = urlencode($dimensionContent->getTitle() ?? '');

        // Use configured share texts or fallback to title
        $twitterText = $settings->getTwitterShareText()
            ? urlencode($settings->getTwitterShareText())
            : $title;

        $facebookText = $settings->getFacebookShareText()
            ? urlencode($settings->getFacebookShareText())
            : $title;

        $linkedInText = $settings->getLinkedInShareText()
            ? urlencode($settings->getLinkedInShareText())
            : $title;

        $allLinks = [
            'facebook' => sprintf('https://www.facebook.com/sharer/sharer.php?u=%s&quote=%s', $url, $facebookText),
            'twitter' => sprintf('https://twitter.com/intent/tweet?url=%s&text=%s', $url, $twitterText),
            'linkedin' => sprintf('https://www.linkedin.com/sharing/share-offsite/?url=%s&summary=%s', $url, $linkedInText),
            'whatsapp' => sprintf('https://wa.me/?text=%s%%20%s', $title, $url),
            'email' => sprintf(
                'mailto:?subject=%s&body=%s',
                $settings->getEmailShareSubject() ? urlencode($settings->getEmailShareSubject()) : $title,
                $settings->getEmailShareBody() ? urlencode($settings->getEmailShareBody()) : $url
            ),
        ];

        return $allLinks;
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

        // Get unlocalized dimension content for dates/location
        $unlocalizedDimensionContent = $this->getUnlocalizedDimensionContent($event);
        if (!$unlocalizedDimensionContent) {
            return [];
        }

        $tags = [
            'og:type' => 'event',
            'og:title' => $dimensionContent->getTitle() ?? '',
            'og:url' => $this->getEventUrl($dimensionContent),
        ];

        // Add start date
        if ($startDate = $unlocalizedDimensionContent->getStartDate()) {
            $tags['event:start_time'] = $startDate->format('c');
        }

        // Add optional properties
        if ($dimensionContent->getSummary()) {
            $tags['og:description'] = $dimensionContent->getSummary();
        }

        if ($image = $dimensionContent->getImage()) {
            $media = $this->mediaManager->getById($image->getId(), $locale);
            $tags['og:image'] = $media->getUrl();
        }

        if ($endDate = $unlocalizedDimensionContent->getEndDate()) {
            $tags['event:end_time'] = $endDate->format('c');
        }

        if ($location = $unlocalizedDimensionContent->getLocation()) {
            $tags['event:location'] = $location->getName();
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

    /**
     * Get unlocalized dimension content from event
     */
    private function getUnlocalizedDimensionContent(Event $event): ?EventDimensionContent
    {
        foreach ($event->getDimensionContents() as $dc) {
            if ($dc->getLocale() === null
                && $dc->getStage() === DimensionContentInterface::STAGE_LIVE
                && $dc->getVersion() === DimensionContentInterface::CURRENT_VERSION
            ) {
                return $dc;
            }
        }

        return null;
    }
}