<?php

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluEventBundle\Entity\Interfaces\ExcerptTranslationInterface;
use Manuxi\SuluEventBundle\Entity\Traits\ExcerptTranslationTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_event_excerpt_translation")
 * @ORM\Entity(repositoryClass="Manuxi\SuluEventBundle\Repository\EventExcerptTranslationRepository")
 */
class EventExcerptTranslation implements ExcerptTranslationInterface
{
    use ExcerptTranslationTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Manuxi\SuluEventBundle\Entity\EventExcerpt", inversedBy="translations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $eventExcerpt;

    public function __construct(EventExcerpt $eventExcerpt, string $locale)
    {
        $this->eventExcerpt = $eventExcerpt;
        $this->setLocale($locale);
        $this->initExcerptTranslationTrait();
    }

    public function getEventExcerpt(): EventExcerpt
    {
        return $this->eventExcerpt;
    }
}
