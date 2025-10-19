<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Manuxi\SuluEventBundle\Repository\EventExcerptTranslationRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Abstracts\Entity\AbstractExcerptTranslation;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\ExcerptTranslationInterface;

#[ORM\Entity(repositoryClass: EventExcerptTranslationRepository::class)]
#[ORM\Table(name: 'app_event_excerpt_translation')]
class EventExcerptTranslation extends AbstractExcerptTranslation implements ExcerptTranslationInterface
{

    #[JoinTable(name: 'app_event_excerpt_categories')]
    protected ?Collection $categories = null;

    #[JoinTable(name: 'app_event_excerpt_tags')]
    protected ?Collection $tags = null;

    #[JoinTable(name: 'app_event_excerpt_icons')]
    protected ?Collection $icons = null;

    #[JoinTable(name: 'app_event_excerpt_images')]
    protected ?Collection $images = null;


    public function __construct(
        #[ORM\ManyToOne(targetEntity: EventExcerpt::class, inversedBy: 'translations')]
        #[JoinColumn(nullable: false)]
        private EventExcerpt $eventExcerpt,
        string $locale,
    ) {
        $this->setLocale($locale);
        $this->initExcerptTranslationTrait();
    }

    public function getEventExcerpt(): EventExcerpt
    {
        return $this->eventExcerpt;
    }
}
