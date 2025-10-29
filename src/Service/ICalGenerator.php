<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;

class ICalGenerator
{
    public function __construct(
        private EventRepository $eventRepository
    ) {}

    /**
     * Generate iCal feed for events
     * @param array $filters - Array of filters (webspace, categories, etc.)
     * @return string - iCal formatted string
     */
    public function generate(array $filters = []): string
    {
        $events = $this->eventRepository->findForIcal($filters);
        
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Sulu Event Bundle//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";

        foreach ($events as $event) {
            $ical .= $this->generateEventBlock($event);
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Generate single event iCal
     */
    public function generateSingle(Event $event): string
    {
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Sulu Event Bundle//EN\r\n";
        $ical .= $this->generateEventBlock($event);
        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Generate VEVENT block for a single event
     */
    private function generateEventBlock(Event $event): string
    {
        $block = "BEGIN:VEVENT\r\n";
        $block .= "UID:" . $event->getId() . "@" . $_SERVER['HTTP_HOST'] . "\r\n";
        $block .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $block .= "DTSTART:" . $event->getStartDate()->format('Ymd\THis\Z') . "\r\n";
        
        if ($event->getEndDate()) {
            $block .= "DTEND:" . $event->getEndDate()->format('Ymd\THis\Z') . "\r\n";
        }
        
        $block .= "SUMMARY:" . $this->escapeString($event->getTitle()) . "\r\n";
        
        if ($event->getSummary()) {
            $block .= "DESCRIPTION:" . $this->escapeString($event->getSummary()) . "\r\n";
        }
        
        if ($event->getLocation()) {
            $location = $event->getLocation();
            $block .= "LOCATION:" . $this->escapeString($location->getName()) . "\r\n";
        }
        
        if ($event->getRoutePath()) {
            $block .= "URL:" . $event->getRoutePath() . "\r\n";
        }
        
        $block .= "END:VEVENT\r\n";

        return $block;
    }

    /**
     * Escape special characters for iCal format
     */
    private function escapeString(string $text): string
    {
        $text = strip_tags($text);
        return str_replace(["\n", "\r", ",", ";"], ['\n', '', '\,', '\;'], $text);
    }
}
