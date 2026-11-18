<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\RemovedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

/**
 * Cleans up after the sieve and the rejections: events whose import entry is "filtered" or "rejected" and that were never
 * published go to the trash of Sulu (like "delete" in the admin, they can be restored). Events that were published, or
 * that another source still maintains, are only listed: that is a decision for an editor.
 */
class ImportPruner
{
    public function __construct(
        private readonly EventImportRepository $importRepository,
        private readonly EventRepository $eventRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TrashManagerInterface $trashManager,
        private readonly DomainEventCollectorInterface $domainEventCollector,
    ) {
    }

    /**
     * @param string[] $states EventImport::STATE_FILTERED and / or STATE_REJECTED
     *
     * @return list<array{uuid: string, title: string, source: string, state: string, reason: string|null, removable: bool, note: string}>
     */
    public function find(array $states): array
    {
        $items = [];
        $seen  = [];
        foreach ($this->importRepository->findWithEventInStates($states) as $import) {
            $event = $import->getEvent();
            if (null === $event || isset($seen[$event->getUuid()])) {
                continue;
            }
            $seen[$event->getUuid()] = true;

            $entries = $this->importRepository->findByEventUuid($event->getUuid());
            $note    = '';
            if ([] !== array_filter($entries, static fn (EventImport $entry): bool => \in_array($entry->getState(), [EventImport::STATE_ACTIVE, EventImport::STATE_VANISHED], true))) {
                $note = 'another source still maintains the event';
            } elseif ($this->wasPublished($event)) {
                $note = 'it was published, please decide in the admin';
            }

            $items[] = [
                'uuid'      => $event->getUuid(),
                'title'     => $this->titleOf($event),
                'source'    => implode(', ', array_map(static fn (EventImport $entry): string => $entry->getSource(), $entries)),
                'state'     => $import->getState(),
                'reason'    => $import->getStateReason(),
                'removable' => '' === $note,
                'note'      => $note,
            ];
        }

        return $items;
    }

    /**
     * Moves the event to the trash. The import entries keep their state ("filtered" stays "filtered", so the candidate is
     * imported again if the rules let it pass later; "rejected" stays "rejected").
     */
    public function remove(string $uuid): bool
    {
        $event = $this->eventRepository->findByUuid($uuid);
        if (null === $event) {
            return false;
        }

        $kept = [];
        foreach ($this->importRepository->findByEventUuid($uuid) as $entry) {
            $kept[] = ['id' => $entry->getId(), 'state' => $entry->getState(), 'reason' => $entry->getStateReason(), 'filtered' => EventImport::STATE_FILTERED === $entry->getState()];
        }

        $title = $this->titleOf($event);
        $this->trashManager->store(Event::RESOURCE_KEY, $event);
        $this->entityManager->remove($event);
        $this->domainEventCollector->collect(new RemovedEvent($uuid, $title));
        $this->entityManager->flush();

        // the listener of the admin deletion marks the entries "rejected"; here the state is what it was
        $table      = $this->entityManager->getClassMetadata(EventImport::class)->getTableName();
        $connection = $this->entityManager->getConnection();
        foreach ($kept as $entry) {
            $connection->executeStatement(
                \sprintf('UPDATE %s SET state = :state, state_reason = :reason, event_uuid = NULL, last_event_uuid = %s WHERE id = :id', $table, $entry['filtered'] ? 'NULL' : 'last_event_uuid'),
                ['state' => $entry['state'], 'reason' => $entry['reason'], 'id' => $entry['id']]
            );
        }
        $this->entityManager->clear();

        return true;
    }

    private function wasPublished(Event $event): bool
    {
        foreach ($event->getDimensionContents() as $row) {
            if (!$row instanceof EventDimensionContent) {
                continue;
            }

            if (DimensionContentInterface::STAGE_LIVE === $row->getStage()
                || null !== $row->getWorkflowPublished()
                || WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $row->getWorkflowPlace()
            ) {
                return true;
            }
        }

        return false;
    }

    private function titleOf(Event $event): string
    {
        foreach ($event->getDimensionContents() as $row) {
            if ($row instanceof EventDimensionContent && DimensionContentInterface::STAGE_DRAFT === $row->getStage() && null !== $row->getLocale() && DimensionContentInterface::CURRENT_VERSION === $row->getVersion() && '' !== trim((string) $row->getTitle())) {
                return (string) $row->getTitle();
            }
        }

        return $event->getUuid();
    }
}
