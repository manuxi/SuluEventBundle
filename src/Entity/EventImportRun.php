<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

/**
 * The result of one run of the import (command, later also the admin): counts and a report with the details that need
 * a look (errors, locked fields, new locations, vanished events).
 */
class EventImportRun
{
    private ?int $id = null;

    private ?\DateTimeImmutable $finishedAt = null;

    private int $created = 0;

    private int $updated = 0;

    private int $unchanged = 0;

    private int $locked = 0;

    private int $vanished = 0;

    private int $rejected = 0;

    private int $errors = 0;

    private int $newLocations = 0;

    /** @var array<string, mixed> */
    private array $report = [];

    public function __construct(
        private \DateTimeImmutable $startedAt,
        private ?string $source = null,
        private bool $dryRun = false,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * @param array{created: int, updated: int, unchanged: int, locked: int, vanished: int, rejected: int, errors: int, newLocations: int} $counts
     * @param array<string, mixed>                                                                                                         $report
     */
    public function finish(\DateTimeImmutable $finishedAt, array $counts, array $report): self
    {
        $this->finishedAt   = $finishedAt;
        $this->created      = $counts['created'];
        $this->updated      = $counts['updated'];
        $this->unchanged    = $counts['unchanged'];
        $this->locked       = $counts['locked'];
        $this->vanished     = $counts['vanished'];
        $this->rejected     = $counts['rejected'];
        $this->errors       = $counts['errors'];
        $this->newLocations = $counts['newLocations'];
        $this->report       = $report;

        return $this;
    }

    /**
     * @return array{created: int, updated: int, unchanged: int, locked: int, vanished: int, rejected: int, errors: int, newLocations: int}
     */
    public function getCounts(): array
    {
        return [
            'created'      => $this->created,
            'updated'      => $this->updated,
            'unchanged'    => $this->unchanged,
            'locked'       => $this->locked,
            'vanished'     => $this->vanished,
            'rejected'     => $this->rejected,
            'errors'       => $this->errors,
            'newLocations' => $this->newLocations,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getReport(): array
    {
        return $this->report;
    }
}
