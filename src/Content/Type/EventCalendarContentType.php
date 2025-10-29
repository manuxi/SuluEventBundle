<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\Type;

use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class EventCalendarContentType extends SimpleContentType
{
    public function __construct(
        private EventRepository $eventRepository
    ) {
        parent::__construct('event_calendar', []);
    }

    /**
     * Prepare calendar data for the template
     */
    public function getContentData(PropertyInterface $property): array
    {
        $data = $property->getValue();
        
        if (!is_array($data)) {
            $data = [];
        }

        $filters = [
            'categories' => $data['categories'] ?? [],
            'tags' => $data['tags'] ?? [],
        ];

        return [
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'text' => $data['text'] ?? null,
            'initialView' => $data['initialView'] ?? 'dayGridMonth',
            'showFilters' => $data['showFilters'] ?? false,
            'filters' => $filters,
        ];
    }
}
