<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;

/**
 * Finds the time zone the stored times of an event are meant in.
 *
 * The database holds the wall clock time as it was entered, without a zone. The zone is, in this order: the one of
 * the event, the one of its location, the configured one (sulu_event.timezone) and finally the PHP default.
 */
class EventTimezoneResolver
{
    public function __construct(private readonly ?string $applicationTimezone = null)
    {
    }

    public function resolve(EventDimensionContent $dimensionContent): \DateTimeZone
    {
        $candidates = [
            $dimensionContent->getTimezone(),
            $dimensionContent->getLocation()?->getTimezone(),
            $this->applicationTimezone,
            date_default_timezone_get(),
        ];

        foreach ($candidates as $name) {
            if (null === $name || '' === $name) {
                continue;
            }

            try {
                return new \DateTimeZone($name);
            } catch (\Exception) {
                // an unknown name stored earlier must not break the output, try the next one
            }
        }

        return new \DateTimeZone('UTC');
    }

    /**
     * The point in time of a stored wall clock time: the time of day is read in the zone of the event, whatever zone
     * the hydrated object happens to carry.
     */
    public function toInstant(\DateTimeInterface $stored, EventDimensionContent $dimensionContent): \DateTimeImmutable
    {
        return new \DateTimeImmutable($stored->format('Y-m-d H:i:s'), $this->resolve($dimensionContent));
    }
}
