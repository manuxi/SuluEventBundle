<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Stub;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stands in for the Sulu category: the nested set fields, a key and translated names are enough for the tests.
 */
#[ORM\Entity]
#[ORM\Table(name: 'stub_category')]
class StubCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * @param array<string, string> $names locale => name
     */
    public function __construct(
        #[ORM\Column(name: 'category_key', type: 'string', length: 255, nullable: true)]
        private ?string $key = null,
        #[ORM\Column(type: 'integer')]
        private int $lft = 0,
        #[ORM\Column(type: 'integer')]
        private int $rgt = 0,
        #[ORM\Column(type: 'integer')]
        private int $depth = 0,
        #[ORM\Column(type: 'json')]
        private array $names = [],
        #[ORM\ManyToOne(targetEntity: self::class)]
        #[ORM\JoinColumn(nullable: true)]
        private ?self $parent = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getLft(): int
    {
        return $this->lft;
    }

    public function getRgt(): int
    {
        return $this->rgt;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getDefaultLocale(): string
    {
        return 'de';
    }

    public function findTranslationByLocale(?string $locale): ?StubCategoryTranslation
    {
        return null !== $locale && isset($this->names[$locale]) ? new StubCategoryTranslation($this->names[$locale]) : null;
    }
}
