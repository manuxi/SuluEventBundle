<?php

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity\Traits;

use Manuxi\SuluEventBundle\Entity\Traits\ImageTrait;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ImageTraitTest extends SuluTestCase
{
    private $mock;
    private $image;

    protected function setUp(): void
    {
        $this->image = $this->prophesize(Media::class);
        $this->mock  = $this->getMockForTrait(ImageTrait::class);
    }

    public function testSetImage(): void
    {
        $this->assertSame($this->mock, $this->mock->setImage($this->image->reveal()));
    }

    public function testGetImage(): void
    {
        $this->mock->setImage($this->image->reveal());
        $this->assertSame($this->image->reveal(), $this->mock->getImage());
    }

    public function testGetImageData(): void
    {
        $this->image->getId()->willReturn(42);
        $this->assertNull($this->mock->getImageData());
        $this->mock->setImage($this->image->reveal());
        $this->assertSame(['id' => 42], $this->mock->getImageData());
    }
}
