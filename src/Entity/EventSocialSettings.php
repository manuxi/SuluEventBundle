<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

class EventSocialSettings
{
    private ?int $id = null;
    private EventDimensionContent $dimensionContent;
    private ?string $twitterShareText = null;
    private ?string $facebookShareText = null;
    private ?string $linkedInShareText = null;
    private ?string $emailShareSubject = null;
    private ?string $emailShareBody = null;

    public function __construct(EventDimensionContent $dimensionContent)
    {
        $this->dimensionContent = $dimensionContent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDimensionContent(): EventDimensionContent
    {
        return $this->dimensionContent;
    }

    public function getTwitterShareText(): ?string
    {
        return $this->twitterShareText;
    }

    public function setTwitterShareText(?string $twitterShareText): self
    {
        $this->twitterShareText = $twitterShareText;
        return $this;
    }

    public function getFacebookShareText(): ?string
    {
        return $this->facebookShareText;
    }

    public function setFacebookShareText(?string $facebookShareText): self
    {
        $this->facebookShareText = $facebookShareText;
        return $this;
    }

    public function getLinkedInShareText(): ?string
    {
        return $this->linkedInShareText;
    }

    public function setLinkedInShareText(?string $linkedInShareText): self
    {
        $this->linkedInShareText = $linkedInShareText;
        return $this;
    }

    public function getEmailShareSubject(): ?string
    {
        return $this->emailShareSubject;
    }

    public function setEmailShareSubject(?string $emailShareSubject): self
    {
        $this->emailShareSubject = $emailShareSubject;
        return $this;
    }

    public function getEmailShareBody(): ?string
    {
        return $this->emailShareBody;
    }

    public function setEmailShareBody(?string $emailShareBody): self
    {
        $this->emailShareBody = $emailShareBody;
        return $this;
    }
}