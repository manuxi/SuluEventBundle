<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Select of the IANA time zones. The first entry, "auto", stands for "no own time zone": the zone of the location is
 * used, otherwise the one of the application (see EventTimezoneResolver). Entities store null for it.
 */
class TimezoneSelect
{
    public const AUTO = 'auto';

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @return list<array{name: string, title: string}>
     */
    public function getValues(): array
    {
        $values = [['name' => self::AUTO, 'title' => $this->translator->trans('sulu_event.timezone.auto', [], 'admin')]];

        foreach (\DateTimeZone::listIdentifiers() as $identifier) {
            $values[] = ['name' => $identifier, 'title' => $identifier];
        }

        return $values;
    }

    public function getDefaultValue(): string
    {
        return self::AUTO;
    }
}
