<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Routing;

use Manuxi\SuluEventBundle\Controller\Website\EventController;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\RouteBundle\Routing\Defaults\RouteDefaultsProviderInterface;

class EventRouteDefaultsProvider implements RouteDefaultsProviderInterface
{

    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository) {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @return mixed[]
     */
    public function getByEntity($entityClass, $id, $locale, $object = null)
    {
        return [
            '_controller' => EventController::class . '::indexAction',
            'event' => $this->eventRepository->findById((int)$id, $locale),
        ];
    }

    public function isPublished($entityClass, $id, $locale)
    {
        return true;
    }

    public function supports($entityClass)
    {
        return Event::class === $entityClass;
    }
}
