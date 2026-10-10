<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Twig;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Service\EventFilterOptions;
use Manuxi\SuluEventBundle\Service\EventSchemaBuilder;
use Manuxi\SuluEventBundle\Service\EventTimezoneResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Functions for the display of events on the website: filter options, times in the zone of the event and the
 * schema.org data.
 */
class EventOutputTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly EventFilterOptions $filterOptions,
        private readonly EventSchemaBuilder $schemaBuilder,
        private readonly EventTimezoneResolver $timezoneResolver,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sulu_event_filter_options', [$this, 'getFilterOptions']),
            new TwigFunction('sulu_event_schema', [$this, 'getSchema']),
            new TwigFunction('sulu_event_datetime', [$this, 'getDateTime']),
            new TwigFunction('sulu_event_timezone', [$this, 'getTimezone']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(string $locale): array
    {
        return $this->filterOptions->getOptions($locale);
    }

    /**
     * The schema.org Event as an array; empty for anything that is not an event.
     *
     * @return array<string, mixed>
     */
    public function getSchema(mixed $event): array
    {
        return $event instanceof EventDimensionContent ? $this->schemaBuilder->build($event) : [];
    }

    /**
     * Start or end of an event as a point in time in the time zone of the event, null if there is none.
     *
     * @param 'start'|'end' $part
     */
    public function getDateTime(mixed $event, string $part = 'start'): ?\DateTimeImmutable
    {
        if (!$event instanceof EventDimensionContent) {
            return null;
        }

        $stored = 'end' === $part ? $event->getEndDate() : $event->getStartDate();

        return null === $stored ? null : $this->timezoneResolver->toInstant($stored, $event);
    }

    public function getTimezone(mixed $event): ?\DateTimeZone
    {
        return $event instanceof EventDimensionContent ? $this->timezoneResolver->resolve($event) : null;
    }
}
