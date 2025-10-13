<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Models;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Manuxi\SuluEventBundle\Domain\Event\Event\CopiedLanguageEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\CreatedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\ModifiedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\PublishedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\RemovedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\UnpublishedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Interfaces\EventModelInterface;
use Manuxi\SuluEventBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Manuxi\SuluEventBundle\Search\Event\EventPublishedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventRemovedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventSavedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventUnpublishedEvent;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventModel implements EventModelInterface
{
    use ArrayPropertyTrait;

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly LocationRepository $locationRepository,
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly ContactRepository $contactRepository,
        private readonly RouteManagerInterface $routeManager,
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getEvent(int $id, ?Request $request = null): Event
    {
        if (null === $request) {
            return $this->findEventById($id);
        }

        return $this->findEventByIdAndLocale($id, $request);
    }

    public function deleteEvent(Event $entity): void
    {
        $this->domainEventCollector->collect(
            new RemovedEvent($entity->getId(), $entity->getTitle() ?? '')
        );
        $this->dispatcher->dispatch(new EventRemovedEvent($entity));
        $this->removeRoutesForEntity($entity);
        $this->eventRepository->remove($entity->getId());
    }

    /**
     * @throws EntityNotFoundException
     */
    public function createEvent(Request $request): Event
    {
        $entity = $this->eventRepository->create((string) $this->getLocaleFromRequest($request));
        $entity = $this->mapDataToEvent($entity, $request->request->all());

        $this->domainEventCollector->collect(
            new CreatedEvent($entity, $request->request->all())
        );

        $entity = $this->eventRepository->save($entity);
        $this->updateRoutesForEntity($entity);

        // explicit flush to save routes persisted by updateRoutesForEntity()
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EventSavedEvent($entity));

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateEvent(int $id, Request $request): Event
    {
        $entity = $this->findEventByIdAndLocale($id, $request);
        $this->dispatcher->dispatch(new EventUnpublishedEvent($entity));

        $entity = $this->mapDataToEvent($entity, $request->request->all());
        $entity = $this->mapSettingsToEvent($entity, $request->request->all());

        $this->domainEventCollector->collect(
            new ModifiedEvent($entity, $request->request->all())
        );
        $entity = $this->eventRepository->save($entity);

        $this->updateRoutesForEntity($entity);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EventSavedEvent($entity));

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function publish(int $id, Request $request): Event
    {
        $entity = $this->findEventByIdAndLocale($id, $request);
        $this->dispatcher->dispatch(new EventUnpublishedEvent($entity));

        $this->domainEventCollector->collect(
            new PublishedEvent($entity, $request->request->all())
        );

        $entity = $this->eventRepository->publish($entity);
        $this->dispatcher->dispatch(new EventPublishedEvent($entity));

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function unpublish(int $id, Request $request): Event
    {
        $entity = $this->findEventByIdAndLocale($id, $request);
        $this->dispatcher->dispatch(new EventUnpublishedEvent($entity));
        $this->domainEventCollector->collect(
            new UnpublishedEvent($entity, $request->request->all())
        );
        $entity = $this->eventRepository->unpublish($entity);
        $this->dispatcher->dispatch(new EventPublishedEvent($entity));

        return $entity;
    }

    public function copyLanguage(int $id, Request $request, string $srcLocale, array $destLocales): Event
    {
        $entity = $this->findEventById($id);
        $entity->setLocale($srcLocale);

        foreach ($destLocales as $destLocale) {
            $entity = $entity->copyToLocale($destLocale);
        }

        // @todo: test with more than one different locale
        $entity->setLocale($this->getLocaleFromRequest($request));

        $this->domainEventCollector->collect(
            new CopiedLanguageEvent($entity, $request->request->all())
        );
        $entity = $this->eventRepository->save($entity);
        $this->dispatcher->dispatch(new EventSavedEvent($entity));

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     */
    private function findEventByIdAndLocale(int $id, Request $request): Event
    {
        $entity = $this->eventRepository->findById($id, (string) $this->getLocaleFromRequest($request));
        if (!$entity) {
            throw new EntityNotFoundException($this->eventRepository->getClassName(), $id);
        }

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     */
    private function findEventById(int $id): Event
    {
        $entity = $this->eventRepository->find($id);
        if (!$entity) {
            throw new EntityNotFoundException($this->eventRepository->getClassName(), $id);
        }

        return $entity;
    }

    private function getLocaleFromRequest(Request $request)
    {
        return $request->query->get('locale');
    }

    /**
     * @throws EntityNotFoundException
     */
    private function mapDataToEvent(Event $entity, array $data): Event
    {
        $title = $this->getProperty($data, 'title');
        if ($title) {
            $entity->setTitle($title);
        }

        $published = $this->getProperty($data, 'published');
        $entity->setPublished($published ?? false);

        $text = $this->getProperty($data, 'text');
        if ($text) {
            $entity->setText($text);
        }

        $routePath = $this->getProperty($data, 'routePath');
        if ($routePath) {
            $entity->setRoutePath($routePath);
        }

        $showAuthor = $this->getProperty($data, 'showAuthor');
        $entity->setShowAuthor($showAuthor ? true : false);

        $showDate = $this->getProperty($data, 'showDate');
        $entity->setShowDate($showDate ? true : false);

        $subtitle = $this->getProperty($data, 'subtitle');
        $entity->setSubtitle($subtitle ?: null);

        $summary = $this->getProperty($data, 'summary');
        $entity->setSummary($summary ?: null);

        $footer = $this->getProperty($data, 'footer');
        $entity->setFooter($footer ?: null);

        $startDate = $this->getProperty($data, 'startDate');
        $entity->setStartDate($startDate ? new \DateTimeImmutable($startDate) : null);

        $endDate = $this->getProperty($data, 'endDate');
        $entity->setEndDate($endDate ? new \DateTimeImmutable($endDate) : null);

        $link = $this->getProperty($data, 'link');
        $entity->setLink($link ?: null);

        $phoneNumber = $this->getProperty($data, 'phoneNumber');
        $entity->setPhoneNumber($phoneNumber ?: null);

        $email = $this->getProperty($data, 'email');
        $entity->setEmail($email ?: null);

        $images = $this->getProperty($data, 'images');
        $entity->setImages($images ?: null);

        $locationId = $this->getProperty($data, 'locationId');
        if ($locationId) {
            $location = $this->locationRepository->findById((int) $locationId);
            if (!$location) {
                throw new EntityNotFoundException($this->locationRepository->getClassName(), $locationId);
            }
            $entity->setLocation($location);
        }

        $imageId = $this->getPropertyMulti($data, ['image', 'id']);
        if ($imageId) {
            $image = $this->mediaRepository->findMediaById((int) $imageId);
            if (!$image) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
            }
            $entity->setImage($image);
        } else {
            $entity->setImage(null);
        }

        $pdfId = $this->getPropertyMulti($data, ['pdf', 'id']);
        if ($pdfId) {
            $pdf = $this->mediaRepository->findMediaById((int) $pdfId);
            if (!$pdf) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $pdfId);
            }
            $entity->setPdf($pdf);
        } else {
            $entity->setPdf(null);
        }

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     */
    private function mapSettingsToEvent(Event $entity, array $data): Event
    {
        // settings (author, authored) changeable
        $authorId = $this->getProperty($data, 'author');
        if ($authorId) {
            $author = $this->contactRepository->findById($authorId);
            if (!$author) {
                throw new EntityNotFoundException($this->contactRepository->getClassName(), $authorId);
            }
            $entity->setAuthor($author);
        } else {
            $entity->setAuthor(null);
        }

        $authored = $this->getProperty($data, 'authored');
        if ($authored) {
            $entity->setAuthored(new \DateTime($authored));
        } else {
            $entity->setAuthored(null);
        }

        return $entity;
    }

    private function updateRoutesForEntity(Event $entity): void
    {
        $this->routeManager->createOrUpdateByAttributes(
            Event::class,
            (string) $entity->getId(),
            $entity->getLocale(),
            $entity->getRoutePath()
        );
    }

    private function removeRoutesForEntity(Event $entity): void
    {
        $routes = $this->routeRepository->findAllByEntity(
            Event::class,
            (string) $entity->getId(),
            $entity->getLocale()
        );

        foreach ($routes as $route) {
            $this->routeRepository->remove($route);
        }
    }
}
