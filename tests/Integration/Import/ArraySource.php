<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Import;

use Manuxi\SuluEventBundle\Import\EventCandidate;
use Manuxi\SuluEventBundle\Import\EventSourceInterface;

/**
 * A source that delivers the candidates it was given (or fails).
 */
class ArraySource implements EventSourceInterface
{
    /**
     * @param list<EventCandidate> $candidates
     */
    public function __construct(
        private readonly string $key,
        private readonly int $priority,
        public array $candidates = [],
        public ?\Throwable $failure = null,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function fetch(): iterable
    {
        if (null !== $this->failure) {
            throw $this->failure;
        }

        yield from $this->candidates;
    }
}
