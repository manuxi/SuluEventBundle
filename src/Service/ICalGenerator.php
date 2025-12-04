<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class ICalGenerator
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    /**
     * Generate iCal feed for events.
     *
     * @param array  $filters Array of filters (locale, categories, tags, etc.)
     * @param string $locale  Locale for content
     *
     * @return string iCal formatted string
     */
    public function generate(array $filters, string $locale): string
    {
        $events = $this->eventRepository->findForIcal($filters);

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Sulu Event Bundle//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";

        foreach ($events as $event) {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $event,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                ]
            );

            if (!$dimensionContent->getTitle()) {
                continue;
            }

            $ical .= $this->generateEventBlock($event, $dimensionContent);
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Generate single event iCal.
     */
    public function generateSingle(Event $event, EventDimensionContent $dimensionContent): string
    {
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Sulu Event Bundle//EN\r\n";
        $ical .= $this->generateEventBlock($event, $dimensionContent);
        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Generate VEVENT block for a single event.
     */
    private function generateEventBlock(Event $event, EventDimensionContent $dimensionContent): string
    {
        $block = "BEGIN:VEVENT\r\n";
        $block .= 'UID:'.$event->getId().'@'.($_SERVER['HTTP_HOST'] ?? 'localhost')."\r\n";
        $block .= 'DTSTAMP:'.gmdate('Ymd\THis\Z')."\r\n";
        $block .= 'DTSTART:'.$event->getStartDate()->format('Ymd\THis\Z')."\r\n";

        if ($event->getEndDate()) {
            $block .= 'DTEND:'.$event->getEndDate()->format('Ymd\THis\Z')."\r\n";
        }

        $block .= 'SUMMARY:'.$this->escapeString($dimensionContent->getTitle() ?? '')."\r\n";

        if ($dimensionContent->getSummary()) {
            $block .= 'DESCRIPTION:'.$this->escapeString($dimensionContent->getSummary())."\r\n";
        }

        if ($event->getLocation()) {
            $location = $event->getLocation();
            $block .= 'LOCATION:'.$this->escapeString($location->getName())."\r\n";
        }

        if ($dimensionContent->getRoute()?->getSlug()) {
            $block .= 'URL:'.$dimensionContent->getRoute()?->getSlug()."\r\n";
        }

        $block .= "END:VEVENT\r\n";

        return $block;
    }

    /**
     * Escape special characters for iCal format.
     */
    private function escapeString(string $text): string
    {
        $text = strip_tags($text);

        return str_replace(["\n", "\r", ',', ';'], ['\n', '', '\,', '\;'], $text);
    }
}
