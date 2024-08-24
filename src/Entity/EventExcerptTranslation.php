<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluEventBundle\Entity\Interfaces\ExcerptTranslationInterface;
use Manuxi\SuluEventBundle\Entity\Traits\ExcerptTranslationTrait;
use Manuxi\SuluEventBundle\Repository\EventExcerptTranslationRepository;

#[ORM\Entity(repositoryClass: EventExcerptTranslationRepository::class)]
#[ORM\Table(name: 'app_event_excerpt_translation')]
class EventExcerptTranslation implements ExcerptTranslationInterface
{
    use ExcerptTranslationTrait;

    #[ORM\ManyToOne(targetEntity: EventExcerpt::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private EventExcerpt $eventExcerpt;

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
