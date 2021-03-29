<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluEventBundle\Entity\Interfaces\SeoTranslationInterface;
use Manuxi\SuluEventBundle\Entity\Traits\SeoTranslationTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_event_seo_translation")
 * @ORM\Entity(repositoryClass="Manuxi\SuluEventBundle\Repository\EventSeoTranslationRepository")
 */
class EventSeoTranslation implements SeoTranslationInterface
{
    use SeoTranslationTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Manuxi\SuluEventBundle\Entity\EventSeo", inversedBy="translations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $eventSeo;

    public function __construct(EventSeo $eventSeo, string $locale)
    {
        $this->eventSeo = $eventSeo;
        $this->setLocale($locale);
    }

    public function getEventSeo(): EventSeo
    {
        return $this->eventSeo;
    }

}
