<?php

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluEventBundle\Entity\Interfaces\ExcerptInterface;
use Manuxi\SuluEventBundle\Entity\Interfaces\ExcerptTranslatableInterface;
use Manuxi\SuluEventBundle\Entity\Traits\ExcerptTrait;
use Manuxi\SuluEventBundle\Entity\Traits\ExcerptTranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_event_excerpt")
 * @ORM\Entity(repositoryClass="Manuxi\SuluEventBundle\Repository\EventExcerptRepository")
 */
class EventExcerpt implements ExcerptInterface, ExcerptTranslatableInterface
{
    use ExcerptTrait;
    use ExcerptTranslatableTrait;

    /**
     * @ORM\OneToOne(targetEntity="Manuxi\SuluEventBundle\Entity\Event", inversedBy="eventExcerpt", cascade={"persist", "remove"})
     * @JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
     *
     * @Serializer\Exclude
     */
    private $event;

    /**
     * @var Collection<string, EventTranslation>
     *
     * @ORM\OneToMany(targetEntity="EventExcerptTranslation", mappedBy="eventExcerpt", cascade={"ALL"}, indexBy="locale")
     *
     * @Serializer\Exclude
     */
    private $translations;

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
     * @return EventExcerptTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?EventExcerptTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): EventExcerptTranslation
    {
        $translation = new EventExcerptTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }

}
