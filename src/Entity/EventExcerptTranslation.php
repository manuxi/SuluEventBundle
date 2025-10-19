<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Manuxi\SuluEventBundle\Repository\EventExcerptTranslationRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Abstracts\Entity\AbstractExcerptTranslation;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\ExcerptTranslationInterface;
use Sulu\Bundle\CategoryBundle\Entity\Category;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

#[ORM\Entity(repositoryClass: EventExcerptTranslationRepository::class)]
#[ORM\Table(name: 'app_event_excerpt_translation')]
class EventExcerptTranslation extends AbstractExcerptTranslation implements ExcerptTranslationInterface
{
    #[ManyToMany(targetEntity: Category::class)]
    #[JoinTable(name: 'app_event_excerpt_categories')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'category_id', referencedColumnName: 'id')]
    protected ?Collection $categories = null;

    #[ManyToMany(targetEntity: TagInterface::class)]
    #[JoinTable(name: 'app_event_excerpt_tags')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    protected ?Collection $tags = null;

    #[ManyToMany(targetEntity: MediaInterface::class)]
    #[JoinTable(name: 'app_event_excerpt_icons')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'icon_id', referencedColumnName: 'id')]
    protected ?Collection $icons = null;

    #[ManyToMany(targetEntity: MediaInterface::class)]
    #[JoinTable(name: 'app_event_excerpt_images')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'image_id', referencedColumnName: 'id')]
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
