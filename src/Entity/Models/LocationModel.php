<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Models;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Manuxi\SuluEventBundle\Domain\Event\Location\CreatedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Location\ModifiedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Location\RemovedEvent;
use Manuxi\SuluEventBundle\Entity\Interfaces\LocationModelInterface;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class LocationModel implements LocationModelInterface
{
    use ArrayPropertyTrait;

    private LocationRepository $locationRepository;
    private MediaRepositoryInterface $mediaRepository;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        LocationRepository $locationRepository,
        MediaRepositoryInterface $mediaRepository,
        DomainEventCollectorInterface $domainEventCollector
    ) {
        $this->locationRepository = $locationRepository;
        $this->mediaRepository = $mediaRepository;
        $this->domainEventCollector = $domainEventCollector;
    }

    /**
     * @param int $id
     * @return Location
     * @throws EntityNotFoundException
     */
    public function getLocation(int $id): Location
    {
        $entity = $this->locationRepository->findById($id);
        if (!$entity) {
            throw new EntityNotFoundException($this->locationRepository->getClassName(), $id);
        }
        return $entity;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteLocation(int $id, string $title): void
    {
        $this->domainEventCollector->collect(
            new RemovedEvent($id, $title)
        );
        $this->locationRepository->remove($id);
    }

    /**
     * @param Request $request
     * @return Location
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createLocation(Request $request): Location
    {
        $entity = $this->locationRepository->create();
        $entity = $this->mapDataToEntity($entity, $request->request->all());
        $this->domainEventCollector->collect(
            new CreatedEvent($entity, $request->request->all())
        );
        return $this->locationRepository->save($entity);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Location
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateLocation(int $id, Request $request): Location
    {
        $entity = $this->getLocation($id);
        $entity = $this->mapDataToEntity($entity, $request->request->all());
        $this->domainEventCollector->collect(
            new ModifiedEvent($entity, $request->request->all())
        );
        return $this->locationRepository->save($entity);
    }

    /**
     * @throws EntityNotFoundException
     */
    private function mapDataToEntity(Location $entity, array $data): Location
    {
        $name = $this->getProperty($data, 'name');
        if ($name) {
            $entity->setName($name);
        }
        $street = $this->getProperty($data, 'street');
        if ($street) {
            $entity->setStreet($street);
        }
        $number = $this->getProperty($data, 'number');
        if ($number) {
            $entity->setNumber($number);
        }
        $city = $this->getProperty($data, 'city');
        if ($city) {
            $entity->setCity($city);
        }
        $postalCode = $this->getProperty($data, 'postalCode');
        if ($postalCode) {
            $entity->setPostalCode($postalCode);
        }
        $state = $this->getProperty($data, 'state');
        if ($state) {
            $entity->setState($state);
        }
        $countryCode = $this->getProperty($data, 'countryCode');
        if ($countryCode) {
            $entity->setCountryCode($countryCode);
        }
        $notes = $this->getProperty($data, 'notes');
        if ($notes) {
            $entity->setNotes($notes);
        }
        $imageId = $this->getPropertyMulti($data, ['image', 'id']);
        if ($imageId) {
            $image = $this->mediaRepository->findMediaById((int) $imageId);
            if (!$image) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
            }
            $entity->setImage($image);
        }
        return $entity;
    }
}
