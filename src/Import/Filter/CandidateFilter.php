<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Filter;

use Manuxi\SuluEventBundle\Import\EventCandidate;

/**
 * The sieve of the import: decides from the rules of the project whether a candidate is worth an event. The bundle is
 * neutral, it knows no countries or regions of its own (see FilterRules). With no rules set everything passes.
 *
 * The rules are the global ones (sulu_event.import.filters), overridden field by field by those of the source (the
 * "filters" of a configured source, or sulu_event.import.source_filters.<key> for any source, also those of the project).
 * A candidate that belongs to a series bypasses the rules named in "series_bypass" (default: countries, unknown_country,
 * online); the rules for the title and the host of the event link always apply.
 */
class CandidateFilter
{
    /** @var array<string, FilterRules> */
    private array $cache = [];

    /**
     * @param array<string, mixed>                $global  sulu_event.import.filters
     * @param array<string, array<string, mixed>> $sources source key => the rules the source sets itself
     *
     * @throws \InvalidArgumentException if a rule is wrong
     */
    public function __construct(
        private readonly array $global = [],
        private readonly array $sources = [],
    ) {
        // the rules are checked when the service is built: a wrong pattern must not surface in the middle of a run
        $this->rulesFor(null);
        foreach (array_keys($this->sources) as $key) {
            $this->rulesFor((string) $key);
        }
    }

    /**
     * @param string|null $sourceKey key of the source ("jsonld.goto-cph", "confs-tech"), null = the global rules
     */
    public function rulesFor(?string $sourceKey): FilterRules
    {
        $cacheKey = $sourceKey ?? '';
        if (!isset($this->cache[$cacheKey])) {
            $layers = [$this->global];
            $path   = 'sulu_event.import.filters';
            if (null !== $sourceKey && isset($this->sources[$sourceKey])) {
                $layers[] = $this->sources[$sourceKey];
                $path     = \sprintf('sulu_event.import.source_filters.%s', $sourceKey);
            }

            $this->cache[$cacheKey] = FilterRules::fromArray(FilterRules::merge(...$layers), $path);
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @param string|null $country the country of the place if the candidate gives only the id of a place (the importer looks it up)
     * @param string|null $type    the type the event would get (the default type of the bundle if the candidate has none)
     */
    public function evaluate(string $sourceKey, EventCandidate $candidate, \DateTimeImmutable $now, ?string $country = null, ?string $type = null): FilterResult
    {
        $rules = $this->rulesFor($sourceKey);

        foreach ($this->hostsOf($candidate) as $host) {
            foreach ($rules->hostExclude as $excluded) {
                if ($host === $excluded || str_ends_with($host, '.' . $excluded)) {
                    return FilterResult::reject('host', \sprintf('The link of the event points to %s, which is excluded.', $excluded));
                }
            }
        }

        foreach ($candidate->titles as $title) {
            foreach ($rules->titleExclude as $pattern) {
                if (1 === preg_match(FilterRules::delimit($pattern), $title)) {
                    return FilterResult::reject('title', \sprintf('The title matches the excluded pattern "%s".', $pattern));
                }
            }
        }

        $inSeries = $this->hasSeries($candidate);
        $applies  = static fn (string $rule): bool => !$inSeries || !$rules->bypasses($rule);

        $onlineOnly = $this->isOnlineOnly($candidate);
        if ($onlineOnly && FilterRules::DENY === $rules->online && $applies(FilterRules::ONLINE)) {
            return FilterResult::reject('online', 'The event takes place online only, without a place.');
        }

        if (!$onlineOnly) {
            $country ??= $this->countryOf($candidate);
            $result = $this->checkCountry($rules, $country, $applies);
            if (null !== $result) {
                return $result;
            }
        }

        $type ??= $candidate->type;
        if ([] !== $rules->types && $applies(FilterRules::TYPES) && !\in_array($type, $rules->types, true)) {
            return FilterResult::reject('type', \sprintf('The type "%s" is not one of the allowed types (%s).', $type ?? '-', implode(', ', $rules->types)));
        }

        if ([] !== $rules->topics && $applies(FilterRules::TOPICS) && [] === array_intersect($rules->topics, $candidate->categoryKeys)) {
            return FilterResult::reject('topic', \sprintf('None of the topics (%s) is one of the allowed topics (%s).', [] === $candidate->categoryKeys ? '-' : implode(', ', $candidate->categoryKeys), implode(', ', $rules->topics)));
        }

        if (null !== $rules->maxMonthsAhead && $applies(FilterRules::MAX_MONTHS_AHEAD) && null !== $candidate->startDate) {
            $limit = $now->modify(\sprintf('+%d months', $rules->maxMonthsAhead));
            if ($candidate->startDate > $limit) {
                return FilterResult::reject('too_far_ahead', \sprintf('The event starts on %s, more than %d months ahead.', $candidate->startDate->format('Y-m-d'), $rules->maxMonthsAhead));
            }
        }

        return FilterResult::pass();
    }

    /**
     * Keeps the tags the project allows (case does not matter, the spelling of the candidate is kept).
     *
     * @param string|null $sourceKey null = the global rules (the MCP tool that changes an event knows no source)
     * @param string[]    $tags
     *
     * @return array{0: string[], 1: string[]} the tags to use and the ones that were dropped
     */
    public function filterTags(?string $sourceKey, array $tags): array
    {
        $allowed = $this->rulesFor($sourceKey)->tagsAllowed;
        if ([] === $allowed) {
            return [$tags, []];
        }

        $lower   = array_map('mb_strtolower', $allowed);
        $kept    = [];
        $dropped = [];
        foreach ($tags as $tag) {
            if (\in_array(mb_strtolower(trim($tag)), $lower, true)) {
                $kept[] = $tag;
            } elseif ('' !== trim($tag)) {
                $dropped[] = $tag;
            }
        }

        return [$kept, $dropped];
    }

    /**
     * A candidate "has a series" if the source names one or gives a key that joins it with the same event of other
     * sources (a list like confs.tech finds the series of an event by its link and gives only that key).
     */
    public function hasSeries(EventCandidate $candidate): bool
    {
        return (null !== $candidate->series && '' !== trim($candidate->series))
            || (null !== $candidate->matchKey && '' !== trim($candidate->matchKey));
    }

    /**
     * "Online only, without a place": the way to take part is online and the candidate has neither a place nor the id
     * of one.
     */
    private function isOnlineOnly(EventCandidate $candidate): bool
    {
        return 'online' === $candidate->attendanceMode && null === $candidate->location && null === $candidate->locationId;
    }

    private function countryOf(EventCandidate $candidate): ?string
    {
        $code = $candidate->location?->countryCode;

        return null !== $code && '' !== trim($code) ? strtoupper(trim($code)) : null;
    }

    /**
     * @param \Closure(string): bool $applies whether a rule counts for this candidate
     */
    private function checkCountry(FilterRules $rules, ?string $country, \Closure $applies): ?FilterResult
    {
        if (null === $country || '' === $country) {
            if (FilterRules::DENY === $rules->unknownCountry && $applies(FilterRules::UNKNOWN_COUNTRY)) {
                return FilterResult::reject('country_unknown', 'The country of the event is not known.');
            }

            return null;
        }

        $country = strtoupper($country);
        if (!$applies(FilterRules::COUNTRIES)) {
            return null;
        }

        if (\in_array($country, $rules->countriesExclude, true)) {
            return FilterResult::reject('country_excluded', \sprintf('The country %s is excluded.', $country));
        }

        if ([] !== $rules->countriesInclude && !\in_array($country, $rules->countriesInclude, true)) {
            return FilterResult::reject('country_not_included', \sprintf('The country %s is not one of the allowed countries.', $country));
        }

        return null;
    }

    /**
     * @return string[] lower case host names of the links of the event
     */
    private function hostsOf(EventCandidate $candidate): array
    {
        $hosts = [];
        foreach ([$candidate->sourceUrl, $candidate->registrationUrl, $candidate->onlineUrl] as $url) {
            $host = null !== $url ? parse_url($url, \PHP_URL_HOST) : null;
            if (\is_string($host) && '' !== $host) {
                $hosts[] = mb_strtolower(rtrim($host, '.'));
            }
        }

        return array_values(array_unique($hosts));
    }
}
