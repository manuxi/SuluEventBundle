<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Import;

use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Import\CandidateLocation;
use Manuxi\SuluEventBundle\Import\Resolver\LocationResolver;
use Manuxi\SuluEventBundle\Import\Resolver\TimezoneGuesser;
use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;

class LocationResolverTest extends AbstractEventDatabaseTestCase
{
    private LocationResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $hub = new Location();
        $hub->setName('Example Hub');
        $hub->setCity('Berlin');
        $hub->setCountryCode('DE');
        $hub->setLocation(['lat' => 52.5200, 'long' => 13.4050, 'zoom' => 16]);
        $this->entityManager->persist($hub);
        $this->entityManager->flush();

        $this->resolver = new LocationResolver($this->entityManager, new TimezoneGuesser());
    }

    public function testFindsByNameAndCityIgnoringCaseAndWhitespace(): void
    {
        $resolved = $this->resolver->resolve(new CandidateLocation('  example   HUB ', city: 'berlin'));

        $this->assertFalse($resolved->created);
        $this->assertSame('Example Hub', $resolved->location->getName());
    }

    public function testSameNameInAnotherCityIsAnotherPlace(): void
    {
        $resolved = $this->resolver->resolve(new CandidateLocation('Example Hub', city: 'Hamburg', countryCode: 'DE'));

        $this->assertTrue($resolved->created);
        $this->assertSame('Hamburg', $resolved->location->getCity());
    }

    public function testNameWithoutCityMatchesOnlyIfUnambiguous(): void
    {
        $this->assertFalse($this->resolver->resolve(new CandidateLocation('Example Hub'))->created);

        $second = new Location();
        $second->setName('Example Hub');
        $second->setCity('Munich');
        $this->entityManager->persist($second);
        $this->entityManager->flush();

        $resolver = new LocationResolver($this->entityManager, new TimezoneGuesser());
        $this->assertTrue($resolver->resolve(new CandidateLocation('Example Hub'), false)->created);
    }

    public function testFindsByCoordinatesWithin300Meters(): void
    {
        $near = $this->resolver->resolve(new CandidateLocation('Different name for the same house', latitude: 52.5215, longitude: 13.4050));
        $this->assertFalse($near->created);
        $this->assertSame('Example Hub', $near->location->getName());

        $far = $this->resolver->resolve(new CandidateLocation('Another house', latitude: 52.5260, longitude: 13.4050));
        $this->assertTrue($far->created, '~670 m away');
    }

    public function testNewLocationGetsAddressAndTimezoneAndIsPersisted(): void
    {
        $resolved = $this->resolver->resolve(new CandidateLocation(
            'Old town',
            'Hauptstr.',
            '12',
            '50679',
            'Cologne',
            'NRW',
            'de',
            50.94,
            6.98,
        ));
        $this->entityManager->flush();

        $this->assertTrue($resolved->created);
        $location = $resolved->location;
        $this->assertNotNull($location->getId());
        $this->assertSame('Hauptstr.', $location->getStreet());
        $this->assertSame('12', $location->getNumber());
        $this->assertSame('DE', $location->getCountryCode());
        $this->assertSame('Europe/Berlin', $location->getTimezone());
        $this->assertSame(50.94, $location->getLatitude());
        $this->assertSame(6.98, $location->getLongitude());
    }

    public function testFallbackTimezoneIsUsedIfThePlaceGivesNoHint(): void
    {
        $resolved = $this->resolver->resolve(new CandidateLocation('Somewhere'), true, 'Asia/Tokyo');

        $this->assertSame('Asia/Tokyo', $resolved->location->getTimezone());
    }

    public function testNewLocationIsFoundAgainInTheSameRun(): void
    {
        $first  = $this->resolver->resolve(new CandidateLocation('Brand new place', city: 'Bonn'));
        $second = $this->resolver->resolve(new CandidateLocation('Brand new place', city: 'Bonn'));

        $this->assertTrue($first->created);
        $this->assertFalse($second->created);
        $this->assertSame($first->location, $second->location);
    }

    public function testDryRunDoesNotPersist(): void
    {
        $resolved = $this->resolver->resolve(new CandidateLocation('Dry place', city: 'Bonn'), false);
        $this->entityManager->flush();

        $this->assertTrue($resolved->created);
        $this->assertNull($resolved->location->getId());
    }
}
