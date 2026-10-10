<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

/**
 * Participation, time and ticket details of an event.
 *
 * Everything except the price info is the same in all locales; like the type and the dates these fields are written
 * to the unlocalized and the localized dimension content (see EventUnlocalizedDataMapper). The apply*Details()
 * methods take over only the keys that are present, so a partial update never clears the other fields.
 */
trait EventDetailsTrait
{
    protected bool $allDay = false;

    /** IANA name; null means "the zone of the location, otherwise the one of the application". */
    protected ?string $timezone = null;

    protected string $status = 'scheduled';

    protected string $attendanceMode = 'offline';

    protected ?string $onlineUrl = null;

    protected ?string $registrationUrl = null;

    /** null = unknown. */
    protected ?bool $isFree = null;

    protected ?string $priceInfo = null;

    protected ?string $organizerName = null;

    protected ?string $organizerUrl = null;

    /** @var string[]|null language codes of the event, e.g. ["de", "en"] */
    protected ?array $languages = null;

    public function getAllDay(): bool
    {
        return $this->allDay;
    }

    public function setAllDay(bool $allDay): self
    {
        $this->allDay = $allDay;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = self::normalizeTimezone($timezone);

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = self::normalizeChoice($status, EventStatus::values(), 'status');

        return $this;
    }

    public function getAttendanceMode(): string
    {
        return $this->attendanceMode;
    }

    public function setAttendanceMode(string $attendanceMode): self
    {
        $this->attendanceMode = self::normalizeChoice($attendanceMode, AttendanceMode::values(), 'attendanceMode');

        return $this;
    }

    public function getOnlineUrl(): ?string
    {
        return $this->onlineUrl;
    }

    public function setOnlineUrl(?string $onlineUrl): self
    {
        $this->onlineUrl = self::normalizeUrl($onlineUrl, 'onlineUrl');

        return $this;
    }

    public function getRegistrationUrl(): ?string
    {
        return $this->registrationUrl;
    }

    public function setRegistrationUrl(?string $registrationUrl): self
    {
        $this->registrationUrl = self::normalizeUrl($registrationUrl, 'registrationUrl');

        return $this;
    }

    public function getIsFree(): ?bool
    {
        return $this->isFree;
    }

    public function setIsFree(?bool $isFree): self
    {
        $this->isFree = $isFree;

        return $this;
    }

    public function getPriceInfo(): ?string
    {
        return $this->priceInfo;
    }

    public function setPriceInfo(?string $priceInfo): self
    {
        $this->priceInfo = self::normalizeText($priceInfo);

        return $this;
    }

    public function getOrganizerName(): ?string
    {
        return $this->organizerName;
    }

    public function setOrganizerName(?string $organizerName): self
    {
        $this->organizerName = self::normalizeText($organizerName);

        return $this;
    }

    public function getOrganizerUrl(): ?string
    {
        return $this->organizerUrl;
    }

    public function setOrganizerUrl(?string $organizerUrl): self
    {
        $this->organizerUrl = self::normalizeUrl($organizerUrl, 'organizerUrl');

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getLanguages(): ?array
    {
        return $this->languages;
    }

    /**
     * @param string[]|string|null $languages
     */
    public function setLanguages(array|string|null $languages): self
    {
        $this->languages = self::normalizeLanguages($languages);

        return $this;
    }

    /**
     * Takes over the fields that are the same in every locale, as far as the keys are present in $data.
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException for an unknown status, attendance mode or time zone or an invalid url
     */
    public function applyUnlocalizedDetails(array $data): void
    {
        if (\array_key_exists('allDay', $data)) {
            $this->allDay = null !== $data['allDay'] && (bool) filter_var($data['allDay'], \FILTER_VALIDATE_BOOLEAN);
        }

        if (\array_key_exists('timezone', $data)) {
            $this->setTimezone(\is_string($data['timezone']) ? $data['timezone'] : null);
        }

        if (\array_key_exists('status', $data)) {
            $this->setStatus(\is_string($data['status']) && '' !== $data['status'] ? $data['status'] : EventStatus::DEFAULT->value);
        }

        if (\array_key_exists('attendanceMode', $data)) {
            $this->setAttendanceMode(
                \is_string($data['attendanceMode']) && '' !== $data['attendanceMode']
                    ? $data['attendanceMode']
                    : AttendanceMode::DEFAULT->value
            );
        }

        foreach (['onlineUrl', 'registrationUrl', 'organizerUrl', 'organizerName'] as $field) {
            if (\array_key_exists($field, $data)) {
                $this->{'set' . ucfirst($field)}(\is_string($data[$field]) ? $data[$field] : null);
            }
        }

        if (\array_key_exists('isFree', $data)) {
            $this->isFree = null === $data['isFree'] || '' === $data['isFree']
                ? null
                : filter_var($data['isFree'], \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE);
        }

        if (\array_key_exists('languages', $data)) {
            $this->setLanguages(\is_array($data['languages']) || \is_string($data['languages']) ? $data['languages'] : null);
        }
    }

    /**
     * Takes over the fields that differ per locale, as far as the keys are present in $data.
     *
     * @param array<string, mixed> $data
     */
    public function applyLocalizedDetails(array $data): void
    {
        if (\array_key_exists('priceInfo', $data)) {
            $this->setPriceInfo(\is_string($data['priceInfo']) ? $data['priceInfo'] : null);
        }
    }

    /**
     * An all-day event covers whole days: it starts at 00:00:00 and, if it has an end, ends at 23:59:59.
     * Does nothing for events that are not all-day.
     */
    public function normalizeAllDayTimes(): void
    {
        if (!$this->allDay) {
            return;
        }

        if (null !== $this->startDate) {
            $this->startDate = $this->startDate->setTime(0, 0, 0);
        }

        if (null !== $this->endDate) {
            $this->endDate = $this->endDate->setTime(23, 59, 59);
        }
    }

    /**
     * Takes over the details of another dimension content of the same event (merging unlocalized into localized
     * content). Fields without a value in the source keep what the target has.
     */
    public function mergeDetailsFrom(self $source): void
    {
        $this->allDay         = $source->allDay;
        $this->status         = $source->status;
        $this->attendanceMode = $source->attendanceMode;

        foreach (['timezone', 'onlineUrl', 'registrationUrl', 'isFree', 'priceInfo', 'organizerName', 'organizerUrl', 'languages'] as $field) {
            if (null !== $source->{$field}) {
                $this->{$field} = $source->{$field};
            }
        }
    }

    protected function copyDetailsFrom(self $source): void
    {
        $this->allDay          = $source->allDay;
        $this->timezone        = $source->timezone;
        $this->status          = $source->status;
        $this->attendanceMode  = $source->attendanceMode;
        $this->onlineUrl       = $source->onlineUrl;
        $this->registrationUrl = $source->registrationUrl;
        $this->isFree          = $source->isFree;
        $this->priceInfo       = $source->priceInfo;
        $this->organizerName   = $source->organizerName;
        $this->organizerUrl    = $source->organizerUrl;
        $this->languages       = $source->languages;
    }

    private static function normalizeText(?string $value): ?string
    {
        $value = null === $value ? null : trim($value);

        return '' === $value ? null : $value;
    }

    /**
     * "auto" is what the admin form shows for "no own time zone".
     */
    private static function normalizeTimezone(?string $timezone): ?string
    {
        $timezone = self::normalizeText($timezone);
        if (null === $timezone || 'auto' === $timezone) {
            return null;
        }

        if (!\in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a valid IANA time zone (e.g. "Europe/Berlin").', $timezone));
        }

        return $timezone;
    }

    /**
     * @param string[] $allowed
     */
    private static function normalizeChoice(string $value, array $allowed, string $field): string
    {
        $value = trim($value);
        if (!\in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a valid %s, use one of: %s.', $value, $field, implode(', ', $allowed)));
        }

        return $value;
    }

    private static function normalizeUrl(?string $url, string $field): ?string
    {
        $url = self::normalizeText($url);
        if (null === $url) {
            return null;
        }

        $scheme = parse_url($url, \PHP_URL_SCHEME);
        if (false === filter_var($url, \FILTER_VALIDATE_URL) || !\in_array(\is_string($scheme) ? mb_strtolower($scheme) : '', ['http', 'https'], true)) {
            throw new \InvalidArgumentException(\sprintf('%s must be a complete http(s) url, got "%s".', $field, $url));
        }

        return $url;
    }

    /**
     * @param string[]|string|null $languages
     *
     * @return string[]|null
     */
    private static function normalizeLanguages(array|string|null $languages): ?array
    {
        if (\is_string($languages)) {
            $languages = explode(',', $languages);
        }

        $normalized = [];
        foreach ($languages ?? [] as $language) {
            $language = \is_string($language) ? mb_strtolower(trim($language)) : '';
            if ('' === $language) {
                continue;
            }

            if (1 !== preg_match('/^[a-z]{2,3}$/', $language)) {
                throw new \InvalidArgumentException(\sprintf('"%s" is not a language code (e.g. "de", "en").', $language));
            }

            $normalized[$language] = $language;
        }

        return [] === $normalized ? null : array_values($normalized);
    }
}
