<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Trash;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Admin\LocationAdmin;
use Manuxi\SuluEventBundle\Domain\Event\Location\RestoredEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;

class LocationTrashItemHandler implements
    StoreTrashItemHandlerInterface,
    RestoreTrashItemHandlerInterface,
    RestoreConfigurationProviderInterface
{
    public function __construct(
        private readonly TrashItemRepositoryInterface $trashItemRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        private readonly DomainEventCollectorInterface $domainEventCollector,
    ) {
    }

    public static function getResourceKey(): string
    {
        return Location::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        /** @var Location $resource */
        $data = [
            // Basic address
            'name' => $resource->getName(),
            'street' => $resource->getStreet(),
            'number' => $resource->getNumber(),
            'postalCode' => $resource->getPostalCode(),
            'city' => $resource->getCity(),
            'state' => $resource->getState(),
            'countryCode' => $resource->getCountryCode(),

            // Contact info
            'email' => $resource->getEmail(),
            'phoneNumber' => $resource->getPhoneNumber(),

            // Additional
            'link' => $resource->getLink(),
            'location' => $resource->getLocation(), // coordinates
            'notes' => $resource->getNotes(),

            // Media
            'imageId' => $resource->getImage()?->getId(),
            'images' => $resource->getImages(),
            'pdfId' => $resource->getPdf()?->getId(),
        ];

        return $this->trashItemRepository->create(
            Location::RESOURCE_KEY,
            (string) $resource->getId(),
            $resource->getName() ?? 'n.a.',
            $data,
            null,
            $options,
            Event::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $data = $trashItem->getRestoreData();
        $locationId = (int) $trashItem->getResourceId();

        $location = new Location();

        // Basic address
        $location->setName($data['name']);

        if (isset($data['street'])) {
            $location->setStreet($data['street']);
        }

        if (isset($data['number'])) {
            $location->setNumber($data['number']);
        }

        if (isset($data['postalCode'])) {
            $location->setPostalCode($data['postalCode']);
        }

        if (isset($data['city'])) {
            $location->setCity($data['city']);
        }

        if (isset($data['state'])) {
            $location->setState($data['state']);
        }

        if (isset($data['countryCode'])) {
            $location->setCountryCode($data['countryCode']);
        }

        // Contact info
        if (isset($data['email'])) {
            $location->setEmail($data['email']);
        }

        if (isset($data['phoneNumber'])) {
            $location->setPhoneNumber($data['phoneNumber']);
        }

        // Additional
        if (isset($data['link'])) {
            $location->setLink($data['link']);
        }

        if (isset($data['location'])) {
            $location->setLocation($data['location']);
        }

        if (isset($data['notes'])) {
            $location->setNotes($data['notes']);
        }

        // Media - image
        if (isset($data['imageId'])) {
            $image = $this->entityManager->find(MediaInterface::class, $data['imageId']);
            if ($image) {
                $location->setImage($image);
            }
        }

        // Media - images (gallery)
        if (isset($data['images'])) {
            $location->setImages($data['images']);
        }

        // Media - PDF
        if (isset($data['pdfId'])) {
            $pdf = $this->entityManager->find(MediaInterface::class, $data['pdfId']);
            if ($pdf) {
                $location->setPdf($pdf);
            }
        }

        $this->domainEventCollector->collect(
            new RestoredEvent($location, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($location, $locationId);

        return $location;
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(
            null,
            LocationAdmin::EDIT_FORM_VIEW,
            ['id' => 'id']
        );
    }
}