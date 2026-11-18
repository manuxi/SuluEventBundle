<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;

/**
 * What the import knows about an event, for the "Source" tab of the admin form.
 */
class EventImportInfoProvider
{
    public function __construct(private readonly EventImportRepository $importRepository)
    {
    }

    /**
     * @return array{source: string, state: string, stateReason: string|null, sourceUrl: string|null, lastSyncedAt: string|null, lockedFields: string[], lastError: string|null, sourceDescription: string|null}|null
     *                                                                                                                                                                                                                null if the event does not come from the import (or the tables of the import do not exist yet)
     */
    public function getInfo(string $eventUuid): ?array
    {
        try {
            /** @var list<EventImport> $entries */
            $entries = $this->importRepository->findByEventUuid($eventUuid);
        } catch (\Throwable) {
            // a project that did not create the tables yet must still be able to edit events
            return null;
        }

        if ([] === $entries) {
            return null;
        }

        $main    = $entries[0];
        $locked  = [];
        $synced  = null;
        $values  = $main->getImportedValues();
        foreach ($entries as $entry) {
            $locked = array_merge($locked, $entry->getLockedFields());
            $at     = $entry->getLastSyncedAt();
            if (null !== $at && (null === $synced || $at > $synced)) {
                $synced = $at;
            }
        }

        return [
            'source'            => implode(', ', array_map(static fn (EventImport $entry): string => $entry->getSource(), $entries)),
            'state'             => $main->getState(),
            'stateReason'       => $main->getStateReason(),
            'sourceUrl'         => $main->getSourceUrl(),
            'lastSyncedAt'      => $synced?->format('Y-m-d H:i'),
            'lockedFields'      => array_values(array_unique($locked)),
            'lastError'         => $main->getLastError(),
            'sourceDescription' => \is_string($values['_sourceDescription'] ?? null) ? $values['_sourceDescription'] : null,
        ];
    }
}
