<?php

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity\Traits;

use Manuxi\SuluEventBundle\Entity\Traits\ExcerptTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ExcerptTraitTest extends SuluTestCase
{
    private $mock;

    protected function setUp(): void
    {
        $this->mock = $this->getMockForTrait(ExcerptTrait::class);

    }

    public function testSetId(): void
    {
        $this->assertSame($this->mock, $this->mock->setid(null));
    }

    public function testGetId(): void
    {
        $id = 42;
        $this->assertNull($this->mock->getId());
        $this->mock->setId($id);
        $this->assertSame($id, $this->mock->getId());
    }
}
