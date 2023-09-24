<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Trash;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Admin\EventAdmin;
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

class LocationTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    private TrashItemRepositoryInterface $trashItemRepository;
    private EntityManagerInterface $entityManager;
    private DoctrineRestoreHelperInterface $doctrineRestoreHelper;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        TrashItemRepositoryInterface   $trashItemRepository,
        EntityManagerInterface         $entityManager,
        DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        DomainEventCollectorInterface  $domainEventCollector
    )
    {
        $this->trashItemRepository = $trashItemRepository;
        $this->entityManager = $entityManager;
        $this->doctrineRestoreHelper = $doctrineRestoreHelper;
        $this->domainEventCollector = $domainEventCollector;
    }

    public static function getResourceKey(): string
    {
        return Location::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        /* @var $resource Location */

        $image = $resource->getImage();

        $data = [
            "name" => $resource->getName(),
            "street" => $resource->getStreet(),
            "number" => $resource->getNumber(),
            "city" => $resource->getCity(),
            "postalCode" => $resource->getPostalCode(),
            "countryCode" => $resource->getCountryCode(),
            "notes" => $resource->getNotes(),
            "imageId" => $image ? $image->getId() : null
        ];
        return $this->trashItemRepository->create(
            Location::RESOURCE_KEY,
            (string)$resource->getId(),
            $resource->getName(),
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
        $locationId = (int)$trashItem->getResourceId();
        $location = new Location();

        $location->setName($data['name']);
        $location->setStreet($data['street']);
        $location->setNumber($data['number']);
        $location->setCity($data['city']);
        $location->setPostalCode($data['postalCode']);
        $location->setCountryCode($data['countryCode']);
        $location->setNotes($data['notes']);

        if($data['imageId']){
            $location->setImage($this->entityManager->find(MediaInterface::class, $data['imageId']));
        }

        $this->domainEventCollector->collect(
            new RestoredEvent($location, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($location, $locationId); //?
        $this->entityManager->flush();
        return $location;
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(
            null,
            EventAdmin::EDIT_FORM_VIEW,
            ['id' => 'id']
        );
    }
}
