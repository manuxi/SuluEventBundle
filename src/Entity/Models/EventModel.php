<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Models;

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
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class EventModel implements EventModelInterface
{
    use ArrayPropertyTrait;

    public function __construct(
        private EventRepository $eventRepository,
        private LocationRepository $locationRepository,
        private MediaRepositoryInterface $mediaRepository,
        private ContactRepository $contactRepository,
        private DomainEventCollectorInterface $domainEventCollector
    ) {}

    /**
     * @throws EntityNotFoundException
     */
    public function getEvent(int $id, Request $request = null): Event
    {
        if(null === $request) {
            return $this->findEventById($id);
        }
        return $this->findEventByIdAndLocale($id, $request);
    }

    /**
     * @param int $id
     * @param string $title
     */
    public function deleteEvent(int $id, string $title): void
    {
        $this->domainEventCollector->collect(
            new RemovedEvent($id, $title)
        );
        $this->eventRepository->remove($id);
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

        return $this->eventRepository->save($entity);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Event
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateEvent(int $id, Request $request): Event
    {
        $entity = $this->findEventByIdAndLocale($id, $request);
        $entity = $this->mapDataToEvent($entity, $request->request->all());
        $entity = $this->mapSettingsToEvent($entity, $request->request->all());

        $this->domainEventCollector->collect(
            new ModifiedEvent($entity, $request->request->all())
        );

        return $this->eventRepository->save($entity);
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function enableEvent(int $id, Request $request): Event
    {
        $entity = $this->findEventByIdAndLocale($id, $request);
        $entity->setEnabled(true);

        $this->domainEventCollector->collect(
            new PublishedEvent($entity, $request->request->all())
        );

        return $this->eventRepository->save($entity);
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function disableEvent(int $id, Request $request): Event
    {
        $entity = $this->findEventByIdAndLocale($id, $request);
        $entity->setEnabled(false);

        $this->domainEventCollector->collect(
            new UnpublishedEvent($entity, $request->request->all())
        );

        return $this->eventRepository->save($entity);
    }

    public function copyLanguage(int $id, Request $request, string $srcLocale, array $destLocales): Event
    {
        $entity = $this->findEventById($id);
        $entity->setLocale($srcLocale);

        foreach($destLocales as $destLocale) {
            $entity = $entity->copyToLocale($destLocale);
        }

        //@todo: test with more than one different locale
        $entity->setLocale($this->getLocaleFromRequest($request));

        $this->domainEventCollector->collect(
            new CopiedLanguageEvent($entity, $request->request->all())
        );

        return $this->eventRepository->save($entity);
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
     * @param Event $entity
     * @param array $data
     * @return Event
     * @throws EntityNotFoundException
     */
    private function mapSettingsToEvent(Event $entity, array $data): Event
    {
        //settings (author, authored) changeable
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
}
