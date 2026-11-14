<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\AutoPublish;

/**
 * The rules of sulu_event.import.auto_publish: which imported events are published without a click and what they need
 * for that.
 */
final class AutoPublishRules
{
    /**
     * @param bool     $series           events that belong to a series qualify
     * @param string[] $sources          source keys; an event of one of them qualifies
     * @param string[] $types            event types; an event of one of them qualifies
     * @param string[] $locales          languages that must be complete (and are published); empty = the languages the event has
     * @param bool     $republishChanges a published event with a new draft is published again if it still meets the rules
     */
    public function __construct(
        public readonly bool $enabled = false,
        public readonly bool $series = false,
        public readonly array $sources = [],
        public readonly array $types = [],
        public readonly array $locales = [],
        public readonly bool $requireCategory = true,
        public readonly bool $requireImage = true,
        public readonly bool $requirePlaceOrOnline = true,
        public readonly bool $republishChanges = false,
    ) {
    }

    /**
     * @param array<string, mixed> $config the processed node sulu_event.import.auto_publish
     */
    public static function fromArray(array $config): self
    {
        $when    = $config['when']    ?? [];
        $require = $config['require'] ?? [];

        return new self(
            (bool) ($config['enabled'] ?? false),
            (bool) ($when['series'] ?? false),
            array_values(array_map('strval', $when['sources'] ?? [])),
            array_values(array_map('strval', $when['types'] ?? [])),
            array_values(array_map('strval', $require['locales'] ?? [])),
            (bool) ($require['category'] ?? true),
            (bool) ($require['image'] ?? true),
            (bool) ($require['place_or_online'] ?? true),
            (bool) ($config['republish_changes'] ?? false),
        );
    }

    /**
     * Whether any condition of "when" is set at all (without one no event qualifies).
     */
    public function hasConditions(): bool
    {
        return $this->series || [] !== $this->sources || [] !== $this->types;
    }
}
