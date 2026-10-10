<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

/**
 * The settings of one configured source (sulu_event.import.sources.ical / .jsonld).
 */
final class SourceConfig
{
    public const KIND_ICAL   = 'ical';
    public const KIND_JSONLD = 'jsonld';

    /** Priorities when none is configured: the official page beats a calendar feed. */
    public const DEFAULT_PRIORITY = [
        self::KIND_JSONLD => 100,
        self::KIND_ICAL   => 50,
    ];

    /**
     * @param string[] $categories keys of Sulu categories every event of the source gets
     * @param string[] $tags       names of tags every event of the source gets
     * @param string[] $languages  language codes of the events of the source
     * @param int|null $image      id of an image of our own (media) every event of the source gets if it has none
     */
    public function __construct(
        public readonly string $kind,
        public readonly string $key,
        public readonly string $url,
        public readonly ?string $type = null,
        public readonly array $categories = [],
        public readonly array $tags = [],
        public readonly string $locale = 'en',
        public readonly ?string $timezone = null,
        public readonly ?string $series = null,
        public readonly ?int $priority = null,
        public readonly array $languages = [],
        public readonly ?int $image = null,
    ) {
    }

    /**
     * The key the events are stored with: "ical.symfony-ug-berlin".
     */
    public function getSourceKey(): string
    {
        return $this->kind . '.' . $this->key;
    }

    public function getPriority(): int
    {
        return $this->priority ?? self::DEFAULT_PRIORITY[$this->kind] ?? 0;
    }

    /**
     * @param array<string, mixed> $config one entry of the configuration tree
     */
    public static function fromArray(string $kind, array $config): self
    {
        return new self(
            $kind,
            (string) $config['key'],
            (string) $config['url'],
            isset($config['type']) ? (string) $config['type'] : null,
            array_values((array) ($config['categories'] ?? [])),
            array_values((array) ($config['tags'] ?? [])),
            (string) ($config['locale'] ?? 'en'),
            isset($config['timezone']) ? (string) $config['timezone'] : null,
            isset($config['series']) ? (string) $config['series'] : null,
            isset($config['priority']) ? (int) $config['priority'] : null,
            array_values((array) ($config['languages'] ?? [])),
            isset($config['image']) ? (int) $config['image'] : null,
        );
    }
}
