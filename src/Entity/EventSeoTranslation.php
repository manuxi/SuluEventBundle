<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluEventBundle\Entity\Interfaces\SeoTranslationInterface;
use Manuxi\SuluEventBundle\Entity\Traits\SeoTranslationTrait;
use Manuxi\SuluEventBundle\Repository\EventSeoTranslationRepository;

#[ORM\Entity(repositoryClass: EventSeoTranslationRepository::class)]
#[ORM\Table(name: 'app_event_seo_translation')]
class EventSeoTranslation implements SeoTranslationInterface
{
    use SeoTranslationTrait;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: EventSeo::class, inversedBy: 'translations')]
        #[ORM\JoinColumn(nullable: false)]
        private EventSeo $eventSeo,
        string $locale
    ) {
        $this->setLocale($locale);
    }

    public function getEventSeo(): EventSeo
    {
        return $this->eventSeo;
    }

}
