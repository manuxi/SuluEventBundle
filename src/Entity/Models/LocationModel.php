<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Models;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Manuxi\SuluEventBundle\Entity\Interfaces\LocationModelInterface;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class LocationModel implements LocationModelInterface
{
    use ArrayPropertyTrait;

    private $locationRepository;
    private $mediaRepository;

    public function __construct(
        LocationRepository $locationRepository,
        MediaRepositoryInterface $mediaRepository
    ) {
        $this->locationRepository = $locationRepository;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @throws EntityNotFoundException
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
        $location = $this->getLocation($id);
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

    /**
     * @throws EntityNotFoundException
     */
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
        $state = $this->getProperty($data, 'state');
        if ($state) {
            $location->setState($state);
        }
        $countryCode = $this->getProperty($data, 'countryCode');
        if ($countryCode) {
            $location->setCountryCode($countryCode);
        }
        $notes = $this->getProperty($data, 'notes');
        if ($notes) {
            $location->setNotes($notes);
        }
        $imageId = $this->getPropertyMulti($data, ['image', 'id']);
        if ($imageId) {
            $image = $this->mediaRepository->findMediaById((int) $imageId);
            if (!$image) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
            }
            $location->setImage($image);
        }
        return $location;
    }
}
