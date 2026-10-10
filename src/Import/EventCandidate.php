<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

/**
 * An event as a source describes it, before it is compared with what is in the database.
 *
 * Start and end are the time of day at the place of the event: the object carries the time zone of the event (its
 * wall clock time is what is stored). Texts are given per locale. Descriptions of the source are no texts: they go
 * to $sourceDescription and only help whoever writes the real text.
 */
final class EventCandidate
{
    /**
     * @param array<string, string> $titles         locale => title
     * @param array<string, string> $subtitles      locale => subtitle
     * @param array<string, string> $summaries      locale => short text
     * @param array<string, string> $texts          locale => text (HTML)
     * @param array<string, string> $priceInfos     locale => price text
     * @param string[]              $languages      language codes of the event ("de", "en")
     * @param string[]              $categoryKeys   keys of Sulu categories
     * @param string[]              $tags           names of tags
     * @param string|null           $series         key of an event series ("ces"); with the year of the start it is the match key
     * @param string|null           $timezone       IANA name of the zone the start and end are meant in, null = the one of the application
     * @param string                $status         see EventStatus
     * @param string                $attendanceMode see AttendanceMode
     */
    public function __construct(
        public string $externalId,
        public array $titles = [],
        public ?\DateTimeImmutable $startDate = null,
        public ?\DateTimeImmutable $endDate = null,
        public bool $allDay = false,
        public ?string $timezone = null,
        public string $status = 'scheduled',
        public string $attendanceMode = 'offline',
        public ?CandidateLocation $location = null,
        public ?string $onlineUrl = null,
        public ?string $registrationUrl = null,
        public ?string $organizerName = null,
        public ?string $organizerUrl = null,
        public ?bool $isFree = null,
        public array $priceInfos = [],
        public array $languages = [],
        public ?string $type = null,
        public array $categoryKeys = [],
        public array $tags = [],
        public array $subtitles = [],
        public array $summaries = [],
        public array $texts = [],
        public ?string $sourceUrl = null,
        public ?string $sourceDescription = null,
        public ?string $series = null,
        public ?string $matchKey = null,
    ) {
    }

    /**
     * @return string[] the locales the candidate has a title in
     */
    public function getLocales(): array
    {
        return array_keys(array_filter($this->titles, static fn (string $title): bool => '' !== trim($title)));
    }

    /**
     * The key that joins the candidates of different sources for the same event: given by the source or built from
     * the series and the year of the start ("ces-2027").
     */
    public function resolveMatchKey(): ?string
    {
        if (null !== $this->matchKey && '' !== $this->matchKey) {
            return mb_strtolower($this->matchKey);
        }

        if (null !== $this->series && '' !== $this->series && null !== $this->startDate) {
            return mb_strtolower($this->series) . '-' . $this->startDate->format('Y');
        }

        return null;
    }

    /**
     * The year the event starts in, used for the url.
     */
    public function getYear(): ?string
    {
        return $this->startDate?->format('Y');
    }
}
