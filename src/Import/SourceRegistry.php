<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

/**
 * All configured sources: the ones built from the configuration and those other bundles or the project tag with
 * "sulu_event.import_source".
 */
class SourceRegistry
{
    /** @var array<string, EventSourceInterface> */
    private array $sources = [];

    /**
     * @param iterable<EventSourceInterface> $sources
     */
    public function __construct(iterable $sources)
    {
        foreach ($sources as $source) {
            $this->sources[$source->getKey()] = $source;
        }
    }

    /**
     * @return array<string, EventSourceInterface>
     */
    public function all(): array
    {
        return $this->sources;
    }

    public function has(string $key): bool
    {
        return isset($this->sources[$key]);
    }

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        return array_keys($this->sources);
    }
}
