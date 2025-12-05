<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Twig;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Service\SocialShareGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SocialShareTwigExtension extends AbstractExtension
{
    public function __construct(
        private SocialShareGenerator $socialShareGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('event_social_shares', [$this, 'getSocialShares']),
            new TwigFunction('event_og_tags', [$this, 'getOpenGraphTags']),
            new TwigFunction('event_twitter_tags', [$this, 'getTwitterTags']),
        ];
    }

    /**
     * Get enabled social share links for an event.
     *
     * @return array<string, string>
     */
    public function getSocialShares(Event $event, string $locale): array
    {
        return $this->socialShareGenerator->generateShareLinks($event, $locale);
    }

    /**
     * Get Open Graph meta tags.
     *
     * @return array<string, string>
     */
    public function getOpenGraphTags(Event $event, string $locale): array
    {
        return $this->socialShareGenerator->generateOpenGraphTags($event, $locale);
    }

    /**
     * Get Twitter Card meta tags.
     *
     * @return array<string, string>
     */
    public function getTwitterTags(Event $event, string $locale): array
    {
        return $this->socialShareGenerator->generateTwitterCardTags($event, $locale);
    }
}