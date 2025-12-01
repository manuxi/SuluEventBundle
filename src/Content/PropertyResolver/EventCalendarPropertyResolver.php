<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\PropertyResolver;

use Sulu\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Content\Application\PropertyResolver\Resolver\PropertyResolverInterface;

class EventCalendarPropertyResolver implements PropertyResolverInterface
{
    public function resolve(mixed $data, string $locale, array $params = []): ContentView
    {
        if (!is_array($data)) {
            $data = [];
        }

        $filters = [
            'categories' => $data['categories'] ?? [],
            'tags' => $data['tags'] ?? [],
        ];

        $calendarData = [
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'text' => $data['text'] ?? null,
            'initialView' => $data['initialView'] ?? 'dayGridMonth',
            'showFilters' => $data['showFilters'] ?? false,
            'filters' => $filters,
            ...$params,
        ];

        return ContentView::create($calendarData, $calendarData);
    }

    public static function getType(): string
    {
        return 'event_calendar';
    }
}
