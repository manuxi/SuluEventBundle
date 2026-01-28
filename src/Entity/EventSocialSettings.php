<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Symfony\Component\Serializer\Attribute\Ignore;

class EventSocialSettings
{
    private ?int $id = null;

    #[Ignore]
    private Event $event;

    private bool $enableSharing = false;
    private ?array $platforms = null;
    private ?string $facebookUrl = null;
    private ?string $twitterHandle = null;
    private ?string $instagramUrl = null;
    private ?string $linkedinUrl = null;
    private ?string $customShareText = null;
    private ?string $targetGroups = null;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Ignore]
    public function getEvent(): Event
    {
        return $this->event;
    }

    public function isEnableSharing(): bool
    {
        return $this->enableSharing;
    }

    public function setEnableSharing(bool $enableSharing): self
    {
        $this->enableSharing = $enableSharing;
        return $this;
    }

    public function getPlatforms(): ?array
    {
        return $this->platforms;
    }

    public function setPlatforms(?array $platforms): self
    {
        $this->platforms = $platforms;
        return $this;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl): self
    {
        $this->facebookUrl = $facebookUrl;
        return $this;
    }

    public function getTwitterHandle(): ?string
    {
        return $this->twitterHandle;
    }

    public function setTwitterHandle(?string $twitterHandle): self
    {
        $this->twitterHandle = $twitterHandle;
        return $this;
    }

    public function getInstagramUrl(): ?string
    {
        return $this->instagramUrl;
    }

    public function setInstagramUrl(?string $instagramUrl): self
    {
        $this->instagramUrl = $instagramUrl;
        return $this;
    }

    public function getLinkedinUrl(): ?string
    {
        return $this->linkedinUrl;
    }

    public function setLinkedinUrl(?string $linkedinUrl): self
    {
        $this->linkedinUrl = $linkedinUrl;
        return $this;
    }

    public function getCustomShareText(): ?string
    {
        return $this->customShareText;
    }

    public function setCustomShareText(?string $customShareText): self
    {
        $this->customShareText = $customShareText;
        return $this;
    }

    public function getTargetGroups(): ?string
    {
        return $this->targetGroups;
    }

    public function setTargetGroups(?string $targetGroups): self
    {
        $this->targetGroups = $targetGroups;
        return $this;
    }
}