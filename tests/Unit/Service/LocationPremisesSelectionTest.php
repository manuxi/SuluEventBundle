<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Manuxi\SuluEventBundle\Service\LocationPremisesSelection;
use PHPUnit\Framework\TestCase;

class LocationPremisesSelectionTest extends TestCase
{
    private LocationPremisesSelection $selection;
    private LocationRepository $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LocationRepository::class);
        $this->selection = new LocationPremisesSelection($this->repository);
    }

    public function testGetValues(): void
    {
        $location = new Location();
        $location->setName('Main Hall');

        $reflection = new \ReflectionClass($location);
        $prop = $reflection->getProperty('id');

        $prop->setValue($location, 1);

        $location->setPremises([
            ['name' => 'Room A'],
            ['name' => 'Room B']
        ]);

        $this->repository->method('findAll')->willReturn([$location]);

        $values = $this->selection->getValues();

        // Should have 3 entries: Main Hall, Room A, Room B
        $this->assertCount(3, $values);

        // Check Main
        $this->assertEquals('Main Hall', $values[0]['name']);
        $this->assertEquals('1', $values[0]['value']);

        // Check Rooms
        $this->assertEquals('Main Hall » Room A', $values[1]['title']);
        $this->assertEquals('1_Room A', $values[1]['name']);
    }
}
