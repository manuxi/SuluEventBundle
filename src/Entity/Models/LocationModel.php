<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Models;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Manuxi\SuluEventBundle\Entity\Interfaces\LocationModelInterface;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class LocationModel implements LocationModelInterface
{
    use ArrayPropertyTrait;

    private $locationRepository;

    public function __construct(
        LocationRepository $locationRepository
    ) {
        $this->locationRepository = $locationRepository;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createLocation(Request $request): Location
    {
        $location = $this->locationRepository->create();
        $location = $this->mapDataToEntity($location, $request->request->all());
        return $this->locationRepository->save($location);
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateLocation(int $id, Request $request): Location
    {
        $location = $this->getLocation($id, $request);
        $location = $this->mapDataToEntity($location, $request->request->all());
        return $this->locationRepository->save($location);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getLocation(int $id): Location
    {
        $location = $this->locationRepository->findById($id);
        if (!$location) {
            throw new EntityNotFoundException($this->locationRepository->getClassName(), $id);
        }
        return $location;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteLocation(int $id): void
    {
        $this->locationRepository->remove($id);
    }

    private function mapDataToEntity(Location $location, array $data): Location
    {
        $name = $this->getProperty($data, 'name');
        if ($name) {
            $location->setName($name);
        }
        $street = $this->getProperty($data, 'street');
        if ($street) {
            $location->setStreet($street);
        }
        $number = $this->getProperty($data, 'number');
        if ($number) {
            $location->setNumber($number);
        }
        $city = $this->getProperty($data, 'city');
        if ($city) {
            $location->setCity($city);
        }
        $postalCode = $this->getProperty($data, 'postalCode');
        if ($postalCode) {
            $location->setPostalCode($postalCode);
        }
        $countryCode = $this->getProperty($data, 'countryCode');
        if ($countryCode) {
            $location->setCountryCode($countryCode);
        }
        $notes = $this->getProperty($data, 'notes');
        if ($notes) {
            $location->setNotes($notes);
        }
        return $location;
    }
}
