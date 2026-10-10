<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

/**
 * A place events can be imported from (an iCal feed, a page with JSON-LD, a list of conferences ...). Services that
 * implement it are tagged "sulu_event.import_source" automatically; projects add their own sources this way.
 */
interface EventSourceInterface
{
    /**
     * Unique key of the source, e.g. "ical.symfony-ug-berlin". It is stored with every imported event and used for
     * the "--source" option of the command.
     */
    public function getKey(): string;

    /**
     * Which source wins when several describe the same event (same match key): the higher number. The mandatory
     * facts (date, place, status) of the source with the highest priority are taken, the others only fill what is empty.
     */
    public function getPriority(): int;

    /**
     * The candidates of the source, upcoming events only.
     *
     * @throws \Throwable if the source cannot be read; the import reports it and goes on with the next source
     *
     * @return iterable<EventCandidate>
     */
    public function fetch(): iterable;
}
