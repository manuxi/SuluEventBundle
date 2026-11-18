<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Translations;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * The admin texts must parse: a duplicate key breaks the whole admin (the cache cannot be built).
 */
class AdminTranslationsTest extends TestCase
{
    public function testGermanAndEnglishParse(): void
    {
        $dir = \dirname(__DIR__, 3) . '/src/Resources/translations/';

        // parseFile throws for a duplicate key
        $de = $this->flatten(Yaml::parseFile($dir . 'admin.de.yaml'));
        $en = $this->flatten(Yaml::parseFile($dir . 'admin.en.yaml'));

        // the texts of the tab "Import" and its button exist in both languages
        foreach (['import.import_now', 'import.run_state', 'import.run_state_title', 'import.run_state_info', 'import.run_state_ready', 'import.run_state_off', 'import.run_state_running'] as $key) {
            $this->assertArrayHasKey('sulu_event.' . $key, $de, $key);
            $this->assertArrayHasKey('sulu_event.' . $key, $en, $key);
        }
    }

    public function testEveryDefaultEventTypeHasAColourAndANameInBothLanguages(): void
    {
        $root  = \dirname(__DIR__, 3) . '/src/Resources/';
        $types = Yaml::parseFile($root . 'config/packages/sulu_event.yaml')['sulu_event']['types'];
        $de    = $this->flatten(Yaml::parseFile($root . 'translations/admin.de.yaml'));
        $en    = $this->flatten(Yaml::parseFile($root . 'translations/admin.en.yaml'));

        $this->assertArrayHasKey('launch', $types);
        foreach ($types as $key => $type) {
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $type['color'], $key);
            $this->assertArrayHasKey('sulu_event.types.' . $key, $de, $key);
            $this->assertArrayHasKey('sulu_event.types.' . $key, $en, $key);
        }
    }

    /**
     * @param array<string, mixed> $tree
     *
     * @return array<string, string>
     */
    private function flatten(array $tree, string $prefix = ''): array
    {
        $flat = [];
        foreach ($tree as $key => $value) {
            if (\is_array($value)) {
                $flat += $this->flatten($value, $prefix . $key . '.');
            } else {
                $flat[$prefix . $key] = (string) $value;
            }
        }

        return $flat;
    }
}
