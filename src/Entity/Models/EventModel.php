<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Models;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Interfaces\EventModelInterface;
use Manuxi\SuluEventBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class EventModel implements EventModelInterface
{
    use ArrayPropertyTrait;

    private $eventRepository;
    private $locationRepository;
    private $mediaRepository;
    private $userRepository;

    public function __construct(
        EventRepository $eventRepository,
        LocationRepository $locationRepository,
        MediaRepositoryInterface $mediaRepository,
        UserRepository $userRepository
    ) {
        $this->locationRepository = $locationRepository;
        $this->mediaRepository = $mediaRepository;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createEvent(Request $request): Event
    {
        $event = $this->eventRepository->create((string) $this->getLocaleFromRequest($request));
        $event = $this->mapDataToEvent($event, $request->request->all());
        return $this->eventRepository->save($event);
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateEvent(int $id, Request $request): Event
    {
        $event = $this->findEventByIdAndLocale($id, $request);
        $event = $this->mapDataToEvent($event, $request->request->all());
        $event = $this->mapSettingsToEvent($event, $request->request->all());
        return $this->eventRepository->save($event);
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function enableEvent(int $id, Request $request): Event
    {
        $event = $this->findEventByIdAndLocale($id, $request);
        switch ($request->query->get('action')) {
            case 'enable':
                $event->setEnabled(true);
                break;
            case 'disable':
                $event->setEnabled(false);
                break;
        }
        return $this->eventRepository->save($event);
    }

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
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteEvent(int $id): void
    {
        $this->eventRepository->remove($id);
    }

    /**
     * @throws EntityNotFoundException
     */
    private function findEventByIdAndLocale(int $id, Request $request): Event
    {
        $event = $this->eventRepository->findById($id, (string) $this->getLocaleFromRequest($request));
        if (!$event) {
            throw new EntityNotFoundException($this->eventRepository->getClassName(), $id);
        }
        return $event;
    }

    /**
     * @throws EntityNotFoundException
     */
    private function findEventById(int $id): Event
    {
        $event = $this->eventRepository->find($id);
        if (!$event) {
            throw new EntityNotFoundException($this->eventRepository->getClassName(), $id);
        }
        return $event;
    }

    private function getLocaleFromRequest(Request $request)
    {
        return $request->query->get('locale');
    }

    /**
     * @throws EntityNotFoundException
     */
    private function mapDataToEvent(Event $event, array $data): Event
    {
        $title = $this->getProperty($data, 'title');
        if ($title) {
            $event->setTitle($title);
        }

        $routePath = $this->getProperty($data, 'routePath');
        if ($routePath) {
            $event->setRoutePath($routePath);
        }

        $teaser = $this->getProperty($data, 'teaser');
        if ($teaser) {
            $event->setTeaser($teaser);
        }

        $description = $this->getProperty($data, 'description');
        if ($description) {
            $event->setDescription($description);
        }

        $startDate = $this->getProperty($data, 'startDate');
        if ($startDate) {
            $event->setStartDate(new \DateTimeImmutable($startDate));
        }

        $endDate = $this->getProperty($data, 'endDate');
        if ($endDate) {
            $event->setStartDate(new \DateTimeImmutable($endDate));
        }

        $locationId = $this->getProperty($data, 'locationId');
        if ($locationId) {
            $location = $this->locationRepository->findById((int) $locationId);
            if (!$location) {
                throw new EntityNotFoundException($this->locationRepository->getClassName(), $locationId);
            }
            $event->setLocation($location);
        }

        $imageId = $this->getPropertyMulti($data, ['image', 'id']);
        if ($imageId) {
            $image = $this->mediaRepository->findMediaById((int) $imageId);
            if (!$image) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
            }
            $event->setImage($image);
        }

        return $event;
    }

    /**
     * @throws EntityNotFoundException
     */
    private function mapSettingsToEvent(Event $event, array $data): Event
    {
        //settings (author, authored) changeable
        $authorId = $this->getProperty($data, 'author');
        if ($authorId) {
            $author = $this->userRepository->findUserById($authorId);
            if (!$author) {
                throw new EntityNotFoundException($this->userRepository->getClassName(), $authorId);
            }
            $event->setAuthor($author);
        }

        $authored = $this->getProperty($data, 'authored');
        if ($authored) {
            $event->setAuthored(new \DateTime($authored));
        }
        return $event;
    }
}
