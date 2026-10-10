<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

/**
 * The link between a candidate of an import source and an event: which event a candidate (source + external id)
 * became, what was imported last (to see changes made in the admin) and whether the candidate may be imported again.
 */
class EventImport
{
    /** The candidate is in the source and linked to an event (or still to be created). */
    public const STATE_ACTIVE = 'active';

    /** The source does not deliver the candidate any more; the event stays. */
    public const STATE_VANISHED = 'vanished';

    /** The event was deleted in the admin; the candidate is not imported again. */
    public const STATE_REJECTED = 'rejected';

    private ?int $id = null;

    private ?string $matchKey = null;

    private ?Event $event = null;

    private ?string $lastEventUuid = null;

    private ?string $contentHash = null;

    /** @var array<string, mixed> */
    private array $importedValues = [];

    /** @var string[] */
    private array $lockedFields = [];

    private string $state = self::STATE_ACTIVE;

    private ?string $sourceUrl = null;

    private int $priority = 0;

    private ?\DateTimeImmutable $lastSeenAt = null;

    private ?\DateTimeImmutable $lastSyncedAt = null;

    private ?string $lastError = null;

    private \DateTimeImmutable $createdAt;

    public function __construct(
        private string $source,
        private string $externalId,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getMatchKey(): ?string
    {
        return $this->matchKey;
    }

    public function setMatchKey(?string $matchKey): self
    {
        $this->matchKey = $matchKey;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * Links the candidate to an event. The uuid is kept apart from the foreign key, which is emptied when the event
     * is deleted: it finds the entry again when the event is restored from the trash.
     */
    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        if (null !== $event) {
            $this->lastEventUuid = $event->getUuid();
        }

        return $this;
    }

    public function getLastEventUuid(): ?string
    {
        return $this->lastEventUuid;
    }

    public function setLastEventUuid(?string $lastEventUuid): self
    {
        $this->lastEventUuid = $lastEventUuid;

        return $this;
    }

    public function getContentHash(): ?string
    {
        return $this->contentHash;
    }

    public function setContentHash(?string $contentHash): self
    {
        $this->contentHash = $contentHash;

        return $this;
    }

    /**
     * @return array<string, mixed> the values written by the last import, by field ("startDate", "title:en", ...)
     */
    public function getImportedValues(): array
    {
        return $this->importedValues;
    }

    /**
     * @param array<string, mixed> $importedValues
     */
    public function setImportedValues(array $importedValues): self
    {
        $this->importedValues = $importedValues;

        return $this;
    }

    /**
     * @return string[] fields that were changed in the admin since the last import and are not overwritten
     */
    public function getLockedFields(): array
    {
        return $this->lockedFields;
    }

    /**
     * @param string[] $lockedFields
     */
    public function setLockedFields(array $lockedFields): self
    {
        $this->lockedFields = array_values(array_unique($lockedFields));

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        if (!\in_array($state, [self::STATE_ACTIVE, self::STATE_VANISHED, self::STATE_REJECTED], true)) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a state of an import entry.', $state));
        }

        $this->state = $state;

        return $this;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl): self
    {
        $this->sourceUrl = $sourceUrl;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getLastSeenAt(): ?\DateTimeImmutable
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(?\DateTimeImmutable $lastSeenAt): self
    {
        $this->lastSeenAt = $lastSeenAt;

        return $this;
    }

    public function getLastSyncedAt(): ?\DateTimeImmutable
    {
        return $this->lastSyncedAt;
    }

    public function setLastSyncedAt(?\DateTimeImmutable $lastSyncedAt): self
    {
        $this->lastSyncedAt = $lastSyncedAt;

        return $this;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(?string $lastError): self
    {
        $this->lastError = $lastError;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
