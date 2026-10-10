<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Stub;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stands in for the Sulu entities (user, media, contact, route) the event mapping only references.
 */
#[ORM\Entity]
#[ORM\Table(name: 'stub_entity')]
class StubEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
