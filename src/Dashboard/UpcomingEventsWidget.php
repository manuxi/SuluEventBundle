<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Dashboard;

use Manuxi\SuluDashboardBundle\Widget\DashboardWidgetInterface;
use Manuxi\SuluDashboardBundle\Widget\WidgetColumns;
use Manuxi\SuluDashboardBundle\Widget\WidgetTypes;
use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Entity\Event;

/**
 * Tile for the dashboard of manuxi/sulu-dashboard-bundle: the next published events and, below them, the number of
 * events that are not published in the language of the toolbar (as footer with a link to the list).
 *
 * This class is only loaded when the dashboard bundle is installed (see SuluEventExtension), so the dashboard stays an
 * optional dependency ("suggest").
 */
final class UpcomingEventsWidget implements DashboardWidgetInterface
{
    public const KEY = 'sulu_event_upcoming';
    public const LIMIT = 5;

    public function __construct(private readonly EventDashboardReader $reader)
    {
    }

    public function getKey(): string
    {
        return self::KEY;
    }

    public function getTitle(): string
    {
        return 'sulu_event_dashboard.upcoming_title';
    }

    public function getType(): string
    {
        return WidgetTypes::LIST;
    }

    public function getSecurityContext(): string
    {
        return Event::SECURITY_CONTEXT;
    }

    public function getColumn(): string
    {
        return WidgetColumns::SIDE;
    }

    public function getPriority(): int
    {
        return 20;
    }

    public function getData(string $locale): array
    {
        $rows = [];

        foreach ($this->reader->upcoming($locale, new \DateTimeImmutable(), self::LIMIT) as $event) {
            $title = trim((string) $event['title']);
            $title = '' === $title ? $event['id'] : $title;

            $rows[] = [
                'title' => $title,
                'text' => $event['location'],
                'meta' => $this->formatDate($event['startDate'], $locale),
                'state' => 'published',
                'link' => [
                    'view' => EventAdmin::EDIT_TABS_VIEW,
                    'attributes' => ['id' => $event['id'], 'locale' => $locale],
                ],
            ];
        }

        $data = [
            'empty' => 'sulu_event_dashboard.upcoming_empty',
            'rows' => $rows,
        ];

        $unpublished = $this->reader->countUnpublished($locale);
        if ($unpublished > 0) {
            $data['footer'] = [
                'title' => ['key' => 'sulu_event_dashboard.unpublished', 'parameters' => ['count' => $unpublished]],
                'rows' => [[
                    'title' => 'sulu_event_dashboard.show_unpublished',
                    'link' => [
                        'view' => EventAdmin::LIST_VIEW,
                        'attributes' => ['locale' => $locale],
                    ],
                ]],
            ];
        }

        return $data;
    }

    private function formatDate(?\DateTimeInterface $date, string $locale): string
    {
        if (null === $date) {
            return '';
        }

        $withTime = '00:00' !== $date->format('H:i');

        if (class_exists(\IntlDateFormatter::class)) {
            $formatted = (new \IntlDateFormatter(
                $locale,
                \IntlDateFormatter::MEDIUM,
                $withTime ? \IntlDateFormatter::SHORT : \IntlDateFormatter::NONE,
                $date->getTimezone()
            ))->format($date);

            if (false !== $formatted) {
                return $formatted;
            }
        }

        return $date->format($withTime ? 'd.m.Y H:i' : 'd.m.Y');
    }
}
