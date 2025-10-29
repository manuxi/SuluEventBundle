<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Twig;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Service\SocialShareGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SocialShareExtension extends AbstractExtension
{
    public function __construct(
        private SocialShareGenerator $socialShareGenerator
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('event_social_shares', [$this, 'getSocialShares']),
            new TwigFunction('event_og_tags', [$this, 'getOpenGraphTags']),
            new TwigFunction('event_twitter_tags', [$this, 'getTwitterTags']),
        ];
    }

    /**
     * Get enabled social share links for an event
     */
    public function getSocialShares(Event $event, string $locale): array
    {
        if (!$event->getEnableSocialShare()) {
            return [];
        }

        $allLinks = $this->socialShareGenerator->generateShareLinks($event, $locale);
        $enabledPlatforms = $event->getSocialPlatforms() ?? ['facebook', 'twitter', 'linkedin', 'whatsapp', 'email'];

        // Filter by enabled platforms
        return array_filter(
            $allLinks,
            fn($key) => in_array($key, $enabledPlatforms, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Get Open Graph meta tags
     */
    public function getOpenGraphTags(Event $event): array
    {
        return $this->socialShareGenerator->generateOpenGraphTags($event);
    }

    /**
     * Get Twitter Card meta tags
     */
    public function getTwitterTags(Event $event): array
    {
        return $this->socialShareGenerator->generateTwitterCardTags($event);
    }
}
