<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluEventBundle\Entity\Interfaces\SeoInterface;
use Manuxi\SuluEventBundle\Entity\Interfaces\SeoTranslatableInterface;
use Manuxi\SuluEventBundle\Entity\Traits\SeoTrait;
use Manuxi\SuluEventBundle\Entity\Traits\SeoTranslatableTrait;
use Manuxi\SuluEventBundle\Repository\EventSeoRepository;

#[ORM\Entity(repositoryClass: EventSeoRepository::class)]
#[ORM\Table(name: 'app_event_seo')]
class EventSeo implements SeoInterface, SeoTranslatableInterface
{
    use SeoTrait;
    use SeoTranslatableTrait;

    #[Serializer\Exclude]
    #[ORM\OneToOne(inversedBy: 'eventSeo', targetEntity: Event::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: "id", nullable: false)]
    private ?Event $event = null;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'eventSeo', targetEntity: EventSeoTranslation::class, cascade: ['all'], indexBy: 'locale')]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return EventSeoTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?EventSeoTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): EventSeoTranslation
    {
        $translation = new EventSeoTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }
}
