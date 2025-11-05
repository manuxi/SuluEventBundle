<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Location;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

class LocationTest extends TestCase
{
    use ProphecyTrait;

    private ?Location $location = null;

    protected function setUp(): void
    {
        $this->location = new Location();
    }

    public function testName(): void
    {
        $this->assertNull($this->location->getName());
        $this->assertSame($this->location, $this->location->setName('Schlosshotel'));
        $this->assertNotNull($this->location->getName());
        $this->assertSame('Schlosshotel', $this->location->getName());
    }

    public function testStreet(): void
    {
        $this->assertNull($this->location->getStreet());
        $this->assertSame($this->location, $this->location->setStreet('Langenhorner Chaussee'));
        $this->assertNotNull($this->location->getStreet());
        $this->assertSame('Langenhorner Chaussee', $this->location->getStreet());
    }

    public function testNumber(): void
    {
        $this->assertNull($this->location->getNumber());
        $this->assertSame($this->location, $this->location->setNumber('42'));
        $this->assertNotNull($this->location->getNumber());
        $this->assertSame('42', $this->location->getNumber());
    }

    public function testPostalCode(): void
    {
        $this->assertNull($this->location->getPostalCode());
        $this->assertSame($this->location, $this->location->setPostalCode('54636'));
        $this->assertNotNull($this->location->getPostalCode());
        $this->assertSame('54636', $this->location->getPostalCode());
    }

    public function testCity(): void
    {
        $this->assertNull($this->location->getCity());
        $this->assertSame($this->location, $this->location->setCity('Wiersdorf'));
        $this->assertNotNull($this->location->getCity());
        $this->assertSame('Wiersdorf', $this->location->getCity());
    }

    public function testState(): void
    {
        $this->assertNull($this->location->getState());
        $this->assertSame($this->location, $this->location->setState('NRW'));
        $this->assertNotNull($this->location->getState());
        $this->assertSame('NRW', $this->location->getState());
    }

    public function testCountryCode(): void
    {
        $this->assertNull($this->location->getCountryCode());
        $this->assertSame($this->location, $this->location->setCountryCode('DE'));
        $this->assertNotNull($this->location->getCountryCode());
        $this->assertSame('DE', $this->location->getCountryCode());
    }

    public function testNotes(): void
    {
        $notes = 'Lorem ipsum dolor sit amet.';
        $this->assertNull($this->location->getNotes());
        $this->assertSame($this->location, $this->location->setNotes($notes));
        $this->assertNotNull($this->location->getNotes());
        $this->assertSame($notes, $this->location->getNotes());
    }

    public function testImage(): void
    {
        $image = $this->prophesize(MediaInterface::class);
        $image->getId()->willReturn(42);

        $this->assertNull($this->location->getImage());
        $this->assertNull($this->location->getImageData());
        $this->assertSame($this->location, $this->location->setImage($image->reveal()));
        $this->assertSame($image->reveal(), $this->location->getImage());
        $this->assertSame(['id' => 42], $this->location->getImageData());
    }
}
