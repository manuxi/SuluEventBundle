<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Trash;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Domain\Event\Event\RestoredEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
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
    public function __construct(
        private TrashItemRepositoryInterface $trashItemRepository,
        private EntityManagerInterface $entityManager,
        private DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        private DomainEventCollectorInterface $domainEventCollector,
    ) {
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
            'locale' => $resource->getLocale(),
            'title' => $resource->getTitle(),
            'subtitle' => $resource->getSubtitle(),
            'summary' => $resource->getSummary(),
            'text' => $resource->getText(),
            'footer' => $resource->getFooter(),
            'startdate' => $resource->getStartDate(),
            'enddate' => $resource->getEndDate(),
            'slug' => $resource->getRoutePath(),
            'published' => $resource->isPublished(),
            'publishedAt' => $resource->getPublishedAt(),
            'ext' => $resource->getExt(),
            'location' => $resource->getLocation()->getId(),

            'imageId' => $image ? $image->getId() : null,
            'pdfIdId' => $pdf ? $pdf->getId() : null,
            'link' => $resource->getLink(),
            'email' => $resource->getEmail(),
            'phone' => $resource->getPhoneNumber(),
            'images' => $resource->getImages(),
            'showAuthor' => $resource->getShowAuthor(),
            'showDate' => $resource->getShowDate(),
        ];

        $restoreType = isset($options['locale']) ? 'translation' : null;

        return $this->trashItemRepository->create(
            Event::RESOURCE_KEY,
            (string) $resource->getId(),
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
        $eventId = (int) $trashItem->getResourceId();
        $event = new Event();
        $event->setLocale($data['locale']);

        $event->setStartDate($data['startdate'] ? new \DateTimeImmutable($data['startdate']['date']) : null);
        $event->setEndDate($data['enddate'] ? new \DateTimeImmutable($data['enddate']['date']) : null);
        $event->setTitle($data['title']);
        $event->setSubtitle($data['subtitle']);
        $event->setSummary($data['summary']);
        $event->setText($data['text']);
        $event->setFooter($data['footer']);
        $event->setRoutePath($data['slug']);
        $event->setPublished($data['published']);
        $event->setPublishedAt($data['publishedAt'] ? new \DateTime($data['publishedAt']['date']) : null);
        $event->setExt($data['ext']);
        $event->setLocation($this->entityManager->find(Location::class, $data['location']));
        $event->setEmail($data['email']);
        $event->setPhoneNumber($data['phone']);
        $event->setImages($data['images']);
        $event->setShowAuthor($data['showAuthor']);
        $event->setShowDate($data['showDate']);

        if ($data['link']) {
            $event->setLink($data['link']);
        }

        if ($data['imageId']) {
            $event->setImage($this->entityManager->find(MediaInterface::class, $data['imageId']));
        }

        $this->domainEventCollector->collect(
            new RestoredEvent($event, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($event, $eventId);
        $this->createRoute($this->entityManager, $eventId, $data['locale'], $event->getRoutePath(), Event::class);
        $this->entityManager->flush();

        return $event;
    }

    private function createRoute(EntityManagerInterface $manager, int $id, string $locale, string $slug, string $class)
    {
        $route = new Route();
        $route->setPath($slug);
        $route->setLocale($locale);
        $route->setEntityClass($class);
        $route->setEntityId($id);
        $route->setHistory(0);
        $route->setCreated(new \DateTime());
        $route->setChanged(new \DateTime());
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
