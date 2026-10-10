<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Base for the small selects of the event form (status, attendance mode): a fixed list of keys, each translated with
 * "<prefix><key>" in the admin domain. Same format as EventTypeSelect for Sulu's single_select field type.
 */
abstract class AbstractTranslatedSelect
{
    public function __construct(protected readonly TranslatorInterface $translator)
    {
    }

    /**
     * @return string[]
     */
    abstract protected function getKeys(): array;

    abstract protected function getTranslationPrefix(): string;

    abstract public function getDefaultValue(): string;

    /**
     * @return list<array{name: string, title: string}>
     */
    public function getValues(): array
    {
        return array_map(
            fn (string $key): array => ['name' => $key, 'title' => $this->getName($key)],
            $this->getKeys()
        );
    }

    /**
     * Translated name of a key; an unknown key is returned as it is.
     */
    public function getName(string $key): string
    {
        if (!\in_array($key, $this->getKeys(), true)) {
            return $key;
        }

        return $this->translator->trans($this->getTranslationPrefix() . $key, [], 'admin');
    }

    public function hasKey(string $key): bool
    {
        return \in_array($key, $this->getKeys(), true);
    }
}
