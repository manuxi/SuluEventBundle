<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluEventBundle\Repository\EventSeoTranslationRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\SeoTranslationInterface;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\SeoTranslationTrait;

#[ORM\Entity(repositoryClass: EventSeoTranslationRepository::class)]
#[ORM\Table(name: 'app_event_seo_translation')]
class EventSeoTranslation implements SeoTranslationInterface
{
    use SeoTranslationTrait;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: EventSeo::class, inversedBy: 'translations')]
        #[ORM\JoinColumn(nullable: false)]
        private EventSeo $eventSeo,
        string $locale,
    ) {
        $this->setLocale($locale);
    }

    public function getEventSeo(): EventSeo
    {
        return $this->eventSeo;
    }
}
