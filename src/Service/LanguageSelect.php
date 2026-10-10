<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Languages;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Multi select of the languages an event is held in: two letter language codes, named in the language of the user.
 */
class LanguageSelect
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @return list<array{name: string, title: string}>
     */
    public function getValues(): array
    {
        $locale = $this->translator instanceof LocaleAwareInterface ? $this->translator->getLocale() : 'en';

        try {
            $names = Languages::getNames($locale);
        } catch (MissingResourceException) {
            $names = Languages::getNames('en');
        }

        $values = [];
        foreach ($names as $code => $name) {
            if (1 === preg_match('/^[a-z]{2}$/', (string) $code)) {
                $values[] = ['name' => (string) $code, 'title' => $name];
            }
        }

        $collator = class_exists(\Collator::class) ? new \Collator($locale) : null;
        usort(
            $values,
            static fn (array $a, array $b): int => $collator ? (int) $collator->compare($a['title'], $b['title']) : strcmp($a['title'], $b['title'])
        );

        return $values;
    }
}
