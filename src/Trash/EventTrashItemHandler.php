<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Trash;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Domain\Event\Event\RestoredEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;

class EventTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
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
        return Event::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        /* @var $resource Event */

        $image = $resource->getImage();
        $pdf = $resource->getPdf();

        $data = [
            "title" => $resource->getTitle(),
            "subtitle" => $resource->getSubtitle(),
            "summary" => $resource->getSummary(),
            "text" => $resource->getText(),
            "footer" => $resource->getFooter(),
            "startdate" => $resource->getStartDate(),
            "enddate" => $resource->getEndDate(),
            "slug" => $resource->getRoutePath(),
            "ext" => $resource->getExt(),
            "enabled" => $resource->isEnabled(),
            "locale" => $resource->getLocale(),
            "location" => $resource->getLocation()->getId(),

            "imageId" => $image ? $image->getId() : null,
            "pdfIdId" => $pdf ? $pdf->getId() : null,
            "url" => $resource->getUrl(),
            "email" => $resource->getEmail(),
            "phone" => $resource->getPhoneNumber(),
            "images" => $resource->getImages(),
        ];

        $restoreType = isset($options['locale']) ? 'translation' : null;

        return $this->trashItemRepository->create(
            Event::RESOURCE_KEY,
            (string)$resource->getId(),
            $resource->getTitle(),
            $data,
            $restoreType,
            $options,
            Event::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $data = $trashItem->getRestoreData();
        $eventId = (int)$trashItem->getResourceId();
        $event = new Event();
        $event->setLocale($data['locale']);

        $event->setStartDate($data['startdate']);
        $event->setEndDate($data['enddate']);
        $event->setTitle($data['title']);
        $event->setSubtitle($data['subtitle']);
        $event->setSummary($data['summary']);
        $event->setText($data['text']);
        $event->setFooter($data['footer']);
        $event->setEnabled($data['enabled']);
        $event->setRoutePath($data['slug']);
        $event->setExt($data['ext']);
        $event->setLocation($this->entityManager->find(Location::class, $data['location']));
        $event->setUrl($data['url']);
        $event->setEmail($data['email']);
        $event->setPhoneNumber($data['phone']);
        $event->setImages($data['images']);

        if($data['imageId']){
            $event->setImage($this->entityManager->find(MediaInterface::class, $data['imageId']));
        }
        if($data['pdfId']){
            $event->setPdf($this->entityManager->find(MediaInterface::class, $data['pdfId']));
        }

        $this->domainEventCollector->collect(
            new RestoredEvent($event, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($event, $eventId);
        $this->createRoute($this->entityManager, $eventId, $event->getRoutePath(), Event::class);
        $this->entityManager->flush();
        return $event;
    }

    private function createRoute(EntityManagerInterface $manager, int $id, string $slug, string $class)
    {
        $route = new Route();
        $route->setPath($slug);
        $route->setLocale('en');
        $route->setEntityClass($class);
        $route->setEntityId($id);
        $route->setHistory(0);
        $route->setCreated(new DateTime());
        $route->setChanged(new DateTime());
        $manager->persist($route);
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
