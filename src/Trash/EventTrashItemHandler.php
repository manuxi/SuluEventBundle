<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Trash;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Domain\Event\Event\RestoredEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Search\Event\EventRemovedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventSavedEvent;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    public function __construct(
        private readonly TrashItemRepositoryInterface $trashItemRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly EventDispatcherInterface $dispatcher,
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

            'imageId' => $image?->getId(),
            'pdfIdId' => $pdf?->getId(),
            'link' => $resource->getLink(),
            'email' => $resource->getEmail(),
            'phone' => $resource->getPhoneNumber(),
            'images' => $resource->getImages(),
            'showAuthor' => $resource->getShowAuthor(),
            'showDate' => $resource->getShowDate(),
        ];

        $restoreType = isset($options['locale']) ? 'translation' : null;

        $this->dispatcher->dispatch(new EventRemovedEvent($resource));

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
        $entityId = (int) $trashItem->getResourceId();
        $entity = new Event();
        $entity->setLocale($data['locale']);

        $entity->setStartDate($data['startdate'] ? new \DateTimeImmutable($data['startdate']['date']) : null);
        $entity->setEndDate($data['enddate'] ? new \DateTimeImmutable($data['enddate']['date']) : null);
        $entity->setTitle($data['title']);
        $entity->setSubtitle($data['subtitle']);
        $entity->setSummary($data['summary']);
        $entity->setText($data['text']);
        $entity->setFooter($data['footer']);
        $entity->setRoutePath($data['slug']);
        $entity->setPublished($data['published']);
        $entity->setPublishedAt($data['publishedAt'] ? new \DateTime($data['publishedAt']['date']) : null);
        $entity->setExt($data['ext']);
        $entity->setLocation($this->entityManager->find(Location::class, $data['location']));
        $entity->setEmail($data['email']);
        $entity->setPhoneNumber($data['phone']);
        $entity->setImages($data['images']);
        $entity->setShowAuthor($data['showAuthor']);
        $entity->setShowDate($data['showDate']);

        if ($data['link']) {
            $entity->setLink($data['link']);
        }

        if ($data['imageId']) {
            $entity->setImage($this->entityManager->find(MediaInterface::class, $data['imageId']));
        }

        $this->domainEventCollector->collect(
            new RestoredEvent($entity, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($entity, $entityId);
        $this->createRoute($this->entityManager, $entityId, $data['locale'], $entity->getRoutePath(), Event::class);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EventSavedEvent($entity));

        return $entity;
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
