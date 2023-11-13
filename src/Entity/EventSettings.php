<?php

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="app_event_settings")
 */
class EventSettings implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = 'event_settings';
    public const FORM_KEY = 'config';
    public const SECURITY_CONTEXT = 'sulu.event.settings';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $toggleHeader = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $toggleHero = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $toggleBreadcrumbs = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pageEvents = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pageEventsPending = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pageEventsExpired = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToggleHeader(): ?bool
    {
        return $this->toggleHeader;
    }

    public function setToggleHeader(?bool $toggleHeader): void
    {
        $this->toggleHeader = $toggleHeader;
    }

    public function getToggleHero(): ?bool
    {
        return $this->toggleHero;
    }

    public function setToggleHero(?bool $toggleHero): void
    {
        $this->toggleHero = $toggleHero;
    }

    public function getToggleBreadcrumbs(): ?bool
    {
        return $this->toggleBreadcrumbs;
    }

    public function setToggleBreadcrumbs(?bool $toggleBreadcrumbs): void
    {
        $this->toggleBreadcrumbs = $toggleBreadcrumbs;
    }

    public function getPageEvents(): ?string
    {
        return $this->pageEvents;
    }

    public function setPageEvents(?string $pageEvents): void
    {
        $this->pageEvents = $pageEvents;
    }

    public function getPageEventsPending(): ?string
    {
        return $this->pageEventsPending;
    }

    public function setPageEventsPending(?string $pageEventsPending): void
    {
        $this->pageEventsPending = $pageEventsPending;
    }

    public function getPageEventsExpired(): ?string
    {
        return $this->pageEventsExpired;
    }

    public function setPageEventsExpired(?string $pageEventsExpired): void
    {
        $this->pageEventsExpired = $pageEventsExpired;
    }

}