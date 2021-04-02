<?php

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity\Traits;

use Manuxi\SuluEventBundle\Entity\Traits\ImageTrait;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ImageTraitTest extends SuluTestCase
{
    private $usingTraitClass;
    private $image;

    protected function setUp(): void
    {
        $this->image           = $this->prophesize(Media::class);
        $this->usingTraitClass = $this->getMockForTrait(ImageTrait::class);
    }

    public function testSetImage(): void
    {
        $this->assertSame($this->usingTraitClass, $this->usingTraitClass->setImage($this->image->reveal()));
    }

    public function testGetImage(): void
    {
        $this->usingTraitClass->setImage($this->image->reveal());
        $this->assertSame($this->image->reveal(), $this->usingTraitClass->getImage());
    }

    public function testGetImageData(): void
    {
        $this->image->getId()->willReturn(42);
        $this->assertNull($this->usingTraitClass->getImageData());
        $this->usingTraitClass->setImage($this->image->reveal());
        $this->assertSame(['id' => 42], $this->usingTraitClass->getImageData());
    }
}
