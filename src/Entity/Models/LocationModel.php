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
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ArrayPropertyTrait;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class LocationModel implements LocationModelInterface
{
    use ArrayPropertyTrait;

    public function __construct(
        private LocationRepository $locationRepository,
        private MediaRepositoryInterface $mediaRepository,
        private DomainEventCollectorInterface $domainEventCollector,
    ) {
    }

    /**
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
        // Name
        $name = $this->getProperty($data, 'name');
        if ($name) {
            $entity->setName($name);
        }

        // Address fields
        $street = $this->getProperty($data, 'street');
        if ($street !== null) {
            $entity->setStreet($street);
        }

        $number = $this->getProperty($data, 'number');
        if ($number !== null) {
            $entity->setNumber($number);
        }

        $city = $this->getProperty($data, 'city');
        if ($city !== null) {
            $entity->setCity($city);
        }

        $postalCode = $this->getProperty($data, 'postalCode');
        if ($postalCode !== null) {
            $entity->setPostalCode($postalCode);
        }

        $state = $this->getProperty($data, 'state');
        if ($state !== null) {
            $entity->setState($state);
        }

        $countryCode = $this->getProperty($data, 'countryCode');
        if ($countryCode !== null) {
            $entity->setCountryCode($countryCode);
        }

        // Contact info
        $email = $this->getProperty($data, 'email');
        if ($email !== null) {
            $entity->setEmail($email);
        }

        $phoneNumber = $this->getProperty($data, 'phoneNumber');
        if ($phoneNumber !== null) {
            $entity->setPhoneNumber($phoneNumber);
        }

        // Link field
        $link = $this->getProperty($data, 'link');
        if ($link !== null) {
            $entity->setLink($link);
        }

        // Location field (coordinates)
        $location = $this->getProperty($data, 'location');
        if ($location !== null) {
            $entity->setLocation($location);
        }

        // Notes
        $notes = $this->getProperty($data, 'notes');
        if ($notes !== null) {
            $entity->setNotes($notes);
        }

        // Image (single)
        $imageId = $this->getPropertyMulti($data, ['image', 'id']);
        if ($imageId) {
            $image = $this->mediaRepository->findMediaById((int) $imageId);
            if (!$image) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
            }
            $entity->setImage($image);
        }

        // PDF
        $pdfId = $this->getPropertyMulti($data, ['pdf', 'id']);
        if ($pdfId) {
            $pdf = $this->mediaRepository->findMediaById((int) $pdfId);
            if (!$pdf) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $pdfId);
            }
            $entity->setPdf($pdf);
        }

        // Images (gallery)
        $images = $this->getProperty($data, 'images');
        if ($images !== null) {
            $entity->setImages($images);
        }

        return $entity;
    }
}
