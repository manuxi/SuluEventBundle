<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\RemovedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\RestoredEvent;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Keeps the import entries in line with what happens to the events in the admin: an event that is deleted is not
 * created again by the import ("rejected"); one that is restored from the trash is imported again ("active").
 *
 * The domain events are dispatched while the entity manager is still flushing, so this works on the connection and
 * leaves the unit of work alone.
 */
class EventImportStateListener implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RemovedEvent::class  => 'onRemoved',
            RestoredEvent::class => 'onRestored',
        ];
    }

    public function onRemoved(RemovedEvent $domainEvent): void
    {
        // the foreign key to the event is emptied by the database, "last_event_uuid" still names the event
        $this->update(
            'UPDATE %s SET state = :new, event_uuid = NULL WHERE last_event_uuid = :uuid AND state != :new',
            ['new' => EventImport::STATE_REJECTED, 'uuid' => $domainEvent->getResourceId()]
        );
    }

    public function onRestored(RestoredEvent $domainEvent): void
    {
        $uuid    = $domainEvent->getEvent()->getUuid();
        $payload = $domainEvent->getEventPayload() ?? [];

        // a restore from the trash creates the event with a new uuid, the payload carries the old one
        $oldUuid = isset($payload['id']) && \is_string($payload['id']) ? $payload['id'] : $uuid;

        $this->update(
            'UPDATE %s SET state = :active, event_uuid = :uuid, last_event_uuid = :uuid WHERE last_event_uuid = :old AND state = :rejected',
            ['active' => EventImport::STATE_ACTIVE, 'rejected' => EventImport::STATE_REJECTED, 'uuid' => $uuid, 'old' => $oldUuid]
        );
    }

    /**
     * @param array<string, string> $parameters
     */
    private function update(string $sql, array $parameters): void
    {
        $table = $this->entityManager->getClassMetadata(EventImport::class)->getTableName();

        $this->entityManager->getConnection()->executeStatement(\sprintf($sql, $table), $parameters);
    }
}
