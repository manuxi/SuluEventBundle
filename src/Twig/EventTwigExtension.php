<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Twig;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EventTwigExtension extends AbstractExtension
{
    public function __construct(private EventRepository $eventRepository)
    {}

    public function getFunctions()
    {
        return [
            new TwigFunction('sulu_resolve_event', [$this, 'resolveEvent']),
            new TwigFunction('sulu_get_events', [$this, 'getEvents'])
        ];
    }

    public function resolveEvent(int $id, string $locale = 'en'): ?Event
    {
        $event = $this->eventRepository->findById($id, $locale);

        return $event ?? null;
    }

    public function getEvents(int $limit = 8, $locale = 'en')
    {
        return $this->eventRepository->findByFilters([], 0, $limit, $limit, $locale);
    }
}