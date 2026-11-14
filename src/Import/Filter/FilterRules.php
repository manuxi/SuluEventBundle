<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Filter;

use Symfony\Component\Intl\Countries;

/**
 * The rules of the sieve for one source (sulu_event.import.filters, overridden field by field by the filters of the
 * source). The bundle brings no lists of its own: every rule is empty (= everything passes) until the project sets it.
 */
final class FilterRules
{
    public const COUNTRIES        = 'countries';
    public const UNKNOWN_COUNTRY  = 'unknown_country';
    public const ONLINE           = 'online';
    public const TYPES            = 'types';
    public const TOPICS           = 'topics';
    public const MAX_MONTHS_AHEAD = 'max_months_ahead';

    public const ALLOW = 'allow';
    public const DENY  = 'deny';

    /** Rules that candidates of a series may bypass (series_bypass). Title and host rules always apply. */
    public const BYPASSABLE = [self::COUNTRIES, self::UNKNOWN_COUNTRY, self::ONLINE, self::TOPICS, self::TYPES, self::MAX_MONTHS_AHEAD];

    public const DEFAULT_BYPASS = [self::COUNTRIES, self::UNKNOWN_COUNTRY, self::ONLINE];

    /** Codes that are no ISO 3166-1 alpha-2 country but are in wide use (the data of the sources and of Wikidata use them): Kosovo. */
    private const USER_ASSIGNED_CODES = ['XK'];

    /** The keys of the configuration, in the order of the documentation. */
    public const KEYS = ['countries', 'unknown_country', 'online', 'types', 'topics', 'title_exclude', 'host_exclude', 'max_months_ahead', 'series_bypass', 'tags_allowed'];

    /**
     * @param string[] $countriesInclude ISO 3166-1 alpha-2 codes (upper case); empty = all
     * @param string[] $countriesExclude
     * @param string[] $types            allowed event types; empty = all
     * @param string[] $topics           category keys, one must match; empty = all
     * @param string[] $titleExclude     regular expressions (without delimiters, case insensitive)
     * @param string[] $hostExclude      host names, subdomains included
     * @param string[] $seriesBypass     rules a candidate with a series does not have to meet
     * @param string[] $tagsAllowed      the tags an event may get (not a rule of the sieve); empty = all
     */
    public function __construct(
        public readonly array $countriesInclude = [],
        public readonly array $countriesExclude = [],
        public readonly string $unknownCountry = self::ALLOW,
        public readonly string $online = self::ALLOW,
        public readonly array $types = [],
        public readonly array $topics = [],
        public readonly array $titleExclude = [],
        public readonly array $hostExclude = [],
        public readonly ?int $maxMonthsAhead = null,
        public readonly array $seriesBypass = self::DEFAULT_BYPASS,
        public readonly array $tagsAllowed = [],
    ) {
    }

    /**
     * Puts the layers on top of each other: a key that a later layer sets replaces the one of the layers before (the
     * parts "include" and "exclude" of "countries" separately).
     *
     * @param array<string, mixed> ...$layers
     *
     * @return array<string, mixed>
     */
    public static function merge(array ...$layers): array
    {
        $merged = [];
        foreach ($layers as $layer) {
            foreach ($layer as $key => $value) {
                if ('countries' === $key && \is_array($value) && isset($merged['countries']) && \is_array($merged['countries'])) {
                    $merged['countries'] = array_replace($merged['countries'], $value);

                    continue;
                }

                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * @param array<string, mixed> $raw  the merged configuration of the rules
     * @param string               $path where the rules are configured, for the error message
     *
     * @throws \InvalidArgumentException if a rule is wrong
     */
    public static function fromArray(array $raw, string $path = 'sulu_event.import.filters'): self
    {
        $unknown = array_diff(array_keys($raw), self::KEYS);
        if ([] !== $unknown) {
            throw new \InvalidArgumentException(\sprintf('%s: unknown rule "%s". Known rules: %s.', $path, implode('", "', $unknown), implode(', ', self::KEYS)));
        }

        $countries = $raw['countries'] ?? [];
        if (!\is_array($countries) || [] !== array_diff(array_keys($countries), ['include', 'exclude'])) {
            throw new \InvalidArgumentException(\sprintf('%s.countries may only have "include" and "exclude" (lists of country codes).', $path));
        }

        $maxMonths = $raw['max_months_ahead'] ?? null;
        if (null !== $maxMonths && (!\is_int($maxMonths) || $maxMonths < 1)) {
            throw new \InvalidArgumentException(\sprintf('%s.max_months_ahead must be a whole number of months (1 or more) or null.', $path));
        }

        $bypass = self::strings($raw['series_bypass'] ?? self::DEFAULT_BYPASS, $path . '.series_bypass');
        if ([] !== array_diff($bypass, self::BYPASSABLE)) {
            throw new \InvalidArgumentException(\sprintf('%s.series_bypass: "%s" cannot be bypassed. Possible: %s.', $path, implode('", "', array_diff($bypass, self::BYPASSABLE)), implode(', ', self::BYPASSABLE)));
        }

        $titleExclude = self::strings($raw['title_exclude'] ?? [], $path . '.title_exclude');
        foreach ($titleExclude as $pattern) {
            if (false === @preg_match(self::delimit($pattern), '')) {
                throw new \InvalidArgumentException(\sprintf('%s.title_exclude: "%s" is not a valid regular expression.', $path, $pattern));
            }
        }

        return new self(
            self::countryCodes($countries['include'] ?? [], $path . '.countries.include'),
            self::countryCodes($countries['exclude'] ?? [], $path . '.countries.exclude'),
            self::choice($raw['unknown_country'] ?? self::ALLOW, $path . '.unknown_country'),
            self::choice($raw['online'] ?? self::ALLOW, $path . '.online'),
            self::strings($raw['types'] ?? [], $path . '.types'),
            self::strings($raw['topics'] ?? [], $path . '.topics'),
            $titleExclude,
            array_map(static fn (string $host): string => mb_strtolower(rtrim($host, '.')), self::strings($raw['host_exclude'] ?? [], $path . '.host_exclude')),
            $maxMonths,
            $bypass,
            self::strings($raw['tags_allowed'] ?? [], $path . '.tags_allowed'),
        );
    }

    /**
     * The expression of a configured pattern: case insensitive, unicode.
     */
    public static function delimit(string $pattern): string
    {
        return '~' . str_replace('~', '\~', $pattern) . '~iu';
    }

    public function bypasses(string $rule): bool
    {
        return \in_array($rule, $this->seriesBypass, true);
    }

    /**
     * @return string[]
     */
    private static function strings(mixed $value, string $path): array
    {
        if (!\is_array($value)) {
            throw new \InvalidArgumentException(\sprintf('%s must be a list.', $path));
        }

        $strings = [];
        foreach ($value as $entry) {
            if (!\is_string($entry) || '' === trim($entry)) {
                throw new \InvalidArgumentException(\sprintf('%s: every entry must be a text that is not empty.', $path));
            }
            $strings[] = trim($entry);
        }

        return array_values(array_unique($strings));
    }

    /**
     * @return string[]
     */
    private static function countryCodes(mixed $value, string $path): array
    {
        $codes = [];
        foreach (self::strings($value, $path) as $code) {
            $code = strtoupper($code);
            if (!Countries::exists($code) && !\in_array($code, self::USER_ASSIGNED_CODES, true)) {
                throw new \InvalidArgumentException(\sprintf('%s: "%s" is not a country code (ISO 3166-1 alpha-2, for example "DE").', $path, $code));
            }
            $codes[] = $code;
        }

        return array_values(array_unique($codes));
    }

    private static function choice(mixed $value, string $path): string
    {
        if (!\in_array($value, [self::ALLOW, self::DENY], true)) {
            throw new \InvalidArgumentException(\sprintf('%s must be "allow" or "deny".', $path));
        }

        return $value;
    }
}
