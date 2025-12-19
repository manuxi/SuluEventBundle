<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Content\DataMapper\EventUnlocalizedDataMapper;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use PHPUnit\Framework\TestCase;

class EventUnlocalizedDataMapperTest extends TestCase
{
    private EventUnlocalizedDataMapper $mapper;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mapper = new EventUnlocalizedDataMapper($this->entityManager);
    }

    public function testMapBasicFields(): void
    {
        $unlocalized = $this->createMock(EventDimensionContent::class);
        $localized = $this->createMock(EventDimensionContent::class);

        $data = [
            'type' => 'workshop',
            'email' => 'test@example.com',
            'phoneNumber' => '123456',
            'startDate' => '2023-01-01',
            'endDate' => '2023-01-02'
        ];

        // Type
        $unlocalized->expects($this->once())->method('setType')->with('workshop');
        $localized->expects($this->once())->method('setType')->with('workshop');

        // Email
        $unlocalized->expects($this->once())->method('setEmail')->with('test@example.com');
        $localized->expects($this->once())->method('setEmail')->with('test@example.com');

        // Dates
        $unlocalized->expects($this->once())->method('setStartDate');
        $localized->expects($this->once())->method('setStartDate');

        $this->mapper->map($unlocalized, $localized, $data);
    }

    public function testMapLocation(): void
    {
        $unlocalized = $this->createMock(EventDimensionContent::class);
        $localized = $this->createMock(EventDimensionContent::class);
        $location = new Location();

        $this->entityManager->method('find')->with(Location::class, 1)->willReturn($location);

        $unlocalized->expects($this->once())->method('setLocation')->with($location);
        $localized->expects($this->once())->method('setLocation')->with($location);

        $this->mapper->map($unlocalized, $localized, ['locationId' => 1]);
    }
}
