<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'app_event_social_settings')]
class EventSocialSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Event::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Event $event;

    // Social Media Sharing
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $enableSocialShare = true;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $socialPlatforms = ['facebook', 'twitter', 'linkedin', 'whatsapp', 'email'];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $customShareText = null;

    // Social Media Profile Links (fÃ¼r "Follow us")
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $facebookUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $twitterHandle = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $instagramUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $linkedinUrl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function isEnableSocialShare(): bool
    {
        return $this->enableSocialShare;
    }

    public function setEnableSocialShare(bool $enableSocialShare): void
    {
        $this->enableSocialShare = $enableSocialShare;
    }

    public function getSocialPlatforms(): ?array
    {
        return $this->socialPlatforms;
    }

    public function setSocialPlatforms(?array $socialPlatforms): void
    {
        $this->socialPlatforms = $socialPlatforms;
    }

    public function getCustomShareText(): ?string
    {
        return $this->customShareText;
    }

    public function setCustomShareText(?string $customShareText): void
    {
        $this->customShareText = $customShareText;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl): void
    {
        $this->facebookUrl = $facebookUrl;
    }

    public function getTwitterHandle(): ?string
    {
        return $this->twitterHandle;
    }

    public function setTwitterHandle(?string $twitterHandle): void
    {
        $this->twitterHandle = $twitterHandle;
    }

    public function getInstagramUrl(): ?string
    {
        return $this->instagramUrl;
    }

    public function setInstagramUrl(?string $instagramUrl): void
    {
        $this->instagramUrl = $instagramUrl;
    }

    public function getLinkedinUrl(): ?string
    {
        return $this->linkedinUrl;
    }

    public function setLinkedinUrl(?string $linkedinUrl): void
    {
        $this->linkedinUrl = $linkedinUrl;
    }


}
