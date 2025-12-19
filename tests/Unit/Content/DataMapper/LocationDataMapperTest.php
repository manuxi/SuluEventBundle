<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Content\DataMapper\LocationDataMapper;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class LocationDataMapperTest extends TestCase
{
    private LocationDataMapper $mapper;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mapper = new LocationDataMapper($this->entityManager);
    }

    public function testMapLocationById(): void
    {
        $localizedContent = $this->createMock(EventDimensionContent::class);
        $location = new Location();

        $this->entityManager->method('find')->with(Location::class, 123)->willReturn($location);

        $localizedContent->expects($this->once())->method('setLocation')->with($location);

        $this->mapper->map(
            $this->createMock(DimensionContentInterface::class),
            $localizedContent,
            ['locationId' => 123]
        );
    }

    public function testMapLocationByArray(): void
    {
        $localizedContent = $this->createMock(EventDimensionContent::class);
        $location = new Location();

        $this->entityManager->method('find')->with(Location::class, 456)->willReturn($location);

        $localizedContent->expects($this->once())->method('setLocation')->with($location);

        $this->mapper->map(
            $this->createMock(DimensionContentInterface::class),
            $localizedContent,
            ['location' => ['id' => 456]]
        );
    }

    public function testMapRemovesLocationIfNull(): void
    {
        $localizedContent = $this->createMock(EventDimensionContent::class);

        $localizedContent->expects($this->once())->method('setLocation')->with(null);

        $this->mapper->map(
            $this->createMock(DimensionContentInterface::class),
            $localizedContent,
            ['locationId' => null]
        );
    }
}
