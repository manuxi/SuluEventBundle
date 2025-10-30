<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

#[ORM\Entity]
#[ORM\Table(name: 'app_event_social_settings')]
#[ORM\Index(columns: ['event_id'], name: 'idx_event_social')]
class EventSocialSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Serializer\Ignore]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'socialSettings', targetEntity: Event::class)]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Event $event = null;

    // Social Media Sharing
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $enableSharing = true;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $platforms = null;

    // Social Media Profile Links (fÃ¼r "Follow us")
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $facebookUrl = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $twitterHandle = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $instagramUrl = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $linkedinUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $customShareText = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $targetGroups = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getEnableSharing(): bool
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

    /**
     * Check if a specific platform is enabled.
     */
    public function isPlatformEnabled(string $platform): bool
    {
        return $this->enableSharing
            && $this->platforms
            && in_array($platform, $this->platforms, true);
    }

    /**
     * Get share URL for a specific platform.
     */
    public function getProfileUrl(string $platform): ?string
    {
        return match ($platform) {
            'facebook' => $this->facebookUrl,
            'instagram' => $this->instagramUrl,
            'linkedin' => $this->linkedinUrl,
            default => null,
        };
    }

    /**
     * Get share text (custom or default from event).
     */
    public function getShareText(?string $default = null): ?string
    {
        return $this->customShareText ?? $default;
    }
}
