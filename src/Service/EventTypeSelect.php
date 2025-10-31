<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Service for managing event types with colors
 * Types are loaded from bundle/app configuration (sulu_event.types)
 */
class EventTypeSelect
{
    private array $types;
    private string $defaultType;

    /**
     * @param TranslatorInterface $translator
     * @param array $types Event types from configuration (injected via %sulu_event.types%)
     * @param string $defaultType Default type key (injected via %sulu_event.default_type%)
     */
    public function __construct(
        private TranslatorInterface $translator,
        array $types = [],
        string $defaultType = 'default'
    ) {
        // Types come from configuration:
        // 1. Bundle default: Resources/config/packages/sulu_event_bundle.yaml
        // 2. App override: config/packages/sulu_event.yaml
        // If app defines types, bundle defaults are completely replaced
        $this->types = $types;
        $this->defaultType = $defaultType;
    }

    /**
     * Returns values in the format required by Sulu's single_select field type.
     *
     * MUST return array with structure:
     * [
     *   'key1' => ['name' => 'translation.key', 'title' => 'Translated Title'],
     *   'key2' => ['name' => 'translation.key', 'title' => 'Translated Title'],
     * ]
     */
    public function getValues(): array
    {
        $values = [];

        foreach ($this->types as $key => $config) {
            $values[] = [
                'name' => $key,
                'title' => $this->translator->trans($config['name'], [], 'admin'),
            ];
        }

        return $values;
    }

    /**
     * Get default value for new events
     */
    public function getDefaultValue(): string
    {
        return $this->defaultType;
    }

    /**
     * Get color for a specific type
     * Falls back to default type color if type not found
     */
    public function getColor(string $type): string
    {
        if (isset($this->types[$type]['color'])) {
            return $this->types[$type]['color'];
        }

        // Fallback to default type
        if (isset($this->types[$this->defaultType]['color'])) {
            return $this->types[$this->defaultType]['color'];
        }

        // Ultimate fallback
        return '#0d6efd';
    }

    /**
     * Get all configured types with their properties
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Get translated name for a type
     * Falls back to default type if type not found
     */
    public function getTypeName(string $type): string
    {
        if (!isset($this->types[$type])) {
            $type = $this->defaultType;
        }

        if (!isset($this->types[$type])) {
            return 'Default';
        }

        return $this->translator->trans($this->types[$type]['name'], [], 'admin');
    }

    /**
     * Check if a type exists in configuration
     */
    public function hasType(string $type): bool
    {
        return isset($this->types[$type]);
    }
}