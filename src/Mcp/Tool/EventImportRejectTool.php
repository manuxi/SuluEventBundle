<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Mcp\Tool;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;
use Mcp\Capability\Attribute\McpTool;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Mcp\Domain\Security\PermissionRequirement;
use Sulu\Mcp\Domain\Security\RequiresPermission;

/**
 * @internal
 */
class EventImportRejectTool
{
    public function __construct(
        private readonly EventImportRepository $importRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_event_import_reject',
        title: 'Reject an imported event',
        description: 'Say that an imported event is not wanted (not relevant, wrong region, duplicate): the import never creates or changes it again, it no longer shows up in sulu_event_import_pending. The event itself is NOT deleted (the tools never delete): it stays as it is until someone removes it in the admin or with the command "sulu:event:import:prune" (drafts only). Name the event by "uuid", or by "source" + "externalId" (as in sulu_event_import_upsert; this also works for a candidate that is not an event yet, so that it is never created). "reason" is required and kept with the entry, write a short, factual reason (for example "outside the target region"). Returns the entries that were rejected.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::EDIT),
    ])]
    public function reject(
        string $reason,
        ?string $uuid = null,
        ?string $source = null,
        ?string $externalId = null,
    ): array {
        try {
            $reason = trim($reason);
            if ('' === $reason) {
                return ['error' => 'A reason is required (a short, factual text, for example "outside the target region").'];
            }

            $hasUuid   = null !== $uuid && '' !== trim($uuid);
            $hasSource = null !== $source && '' !== trim($source) && null !== $externalId && '' !== trim($externalId);
            if ($hasUuid === $hasSource) {
                return ['error' => 'Give either "uuid" or both "source" and "externalId".'];
            }

            if ($hasUuid) {
                $entries = $this->importRepository->findByEventUuid(trim((string) $uuid));
                if ([] === $entries) {
                    return ['error' => \sprintf('The event "%s" does not come from the import, there is nothing to reject. Delete it in the admin if it is not wanted.', $uuid)];
                }
            } else {
                $entry = $this->importRepository->findBySourceAndExternalId(trim((string) $source), trim((string) $externalId));
                if (null === $entry) {
                    // not an event yet: the entry keeps a later import from creating it
                    $entry = new EventImport(trim((string) $source), trim((string) $externalId));
                    $this->importRepository->add($entry);
                }
                $entries = [$entry];
            }

            $rejected = [];
            foreach ($entries as $entry) {
                $entry->setState(EventImport::STATE_REJECTED, $reason);
                $rejected[] = ['source' => $entry->getSource(), 'externalId' => $entry->getExternalId(), 'eventUuid' => $entry->getEvent()?->getUuid()];
            }
            $this->entityManager->flush();

            return ['success' => true, 'rejected' => $rejected, 'reason' => $reason, 'message' => 'The import leaves the event alone from now on. The event is not deleted.'];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to reject the event: %s', $e->getMessage())];
        }
    }
}
