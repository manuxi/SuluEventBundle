<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Twig;

use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EventTypeTwigExtension extends AbstractExtension
{
    public function __construct(
        private EventTypeSelect $eventTypeSelect,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sulu_event_type_color', [$this, 'getEventTypeColor']),
            new TwigFunction('sulu_event_type_name', [$this, 'getEventTypeName']),
        ];
    }

    /**
     * Get color for event type
     */
    public function getEventTypeColor(?string $type): string
    {
        if (!$type) {
            return $this->eventTypeSelect->getColor('default');
        }

        return $this->eventTypeSelect->getColor($type);
    }

    /**
     * Get translated name for event type
     */
    public function getEventTypeName(?string $type): string
    {
        if (!$type) {
            return $this->eventTypeSelect->getTypeName('default');
        }

        return $this->eventTypeSelect->getTypeName($type);
    }
}