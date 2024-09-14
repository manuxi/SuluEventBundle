<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Routing;

use Manuxi\SuluEventBundle\Controller\Website\EventController;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\RouteBundle\Routing\Defaults\RouteDefaultsProviderInterface;

class EventRouteDefaultsProvider implements RouteDefaultsProviderInterface
{
    public function __construct(private EventRepository $eventRepository)
    {}

    public function getByEntity($entityClass, $id, $locale, $object = null): array
    {
        return [
            '_controller' => EventController::class . '::indexAction',
            'event' => $this->eventRepository->findById((int)$id, $locale),
        ];
    }

    public function isPublished($entityClass, $id, $locale): bool
    {
        $entity = $this->eventRepository->findById((int)$id, $locale);
        return $entity->isEnabled();
    }

    public function supports($entityClass): bool
    {
        return Event::class === $entityClass;
    }
}
