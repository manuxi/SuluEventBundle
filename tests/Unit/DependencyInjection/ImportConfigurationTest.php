<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\DependencyInjection;

use Manuxi\SuluEventBundle\DependencyInjection\SuluEventExtension;
use Manuxi\SuluEventBundle\Import\Source\ICalSource;
use Manuxi\SuluEventBundle\Import\Source\JsonLdSource;
use Manuxi\SuluEventBundle\Import\SourceConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ImportConfigurationTest extends TestCase
{
    public function testImportIsOffByDefaultAndNeedsNothing(): void
    {
        $container = $this->load([]);

        $this->assertFalse($container->getParameter('sulu_event.import.enabled'));
        $this->assertNull($container->getParameter('sulu_event.import.user'));
        $this->assertFalse($container->getParameter('sulu_event.import.auto_update_published_dates'));
        $this->assertSame('/events', $container->getParameter('sulu_event.import.url_prefix'));
        $this->assertStringContainsString('SuluEventBundle', $container->getParameter('sulu_event.import.user_agent'));
        $this->assertFalse($container->hasDefinition('sulu_event.import.source.ical.any'));
    }

    public function testSourcesBecomeTaggedServices(): void
    {
        $container = $this->load(['import' => [
            'enabled'                     => true,
            'user'                        => 'event-import',
            'user_agent'                  => 'Example-Import (+https://example.com/contact)',
            'auto_update_published_dates' => true,
            'sources'                     => [
                'ical' => [
                    ['key' => 'ug-berlin', 'url' => 'https://example.com/events.ics', 'type' => 'default', 'categories' => ['web-php'], 'tags' => ['Symfony'], 'locale' => 'de', 'timezone' => 'Europe/Berlin'],
                ],
                'jsonld' => [
                    ['key' => 'conf', 'url' => 'https://conf.example.com/', 'series' => 'conf', 'priority' => 120],
                ],
            ],
        ]]);

        $this->assertTrue($container->getParameter('sulu_event.import.enabled'));
        $this->assertSame('event-import', $container->getParameter('sulu_event.import.user'));
        $this->assertTrue($container->getParameter('sulu_event.import.auto_update_published_dates'));
        $this->assertSame('Example-Import (+https://example.com/contact)', $container->getParameter('sulu_event.import.user_agent'));

        $ical = $container->getDefinition('sulu_event.import.source.ical.ug-berlin');
        $this->assertSame(ICalSource::class, $ical->getClass());
        $this->assertArrayHasKey('sulu_event.import_source', $ical->getTags());

        $jsonld = $container->getDefinition('sulu_event.import.source.jsonld.conf');
        $this->assertSame(JsonLdSource::class, $jsonld->getClass());

        $config = $ical->getArgument(0);
        $this->assertSame([SourceConfig::class, 'fromArray'], $config->getFactory());
        $this->assertSame('ical', $config->getArgument(0));
        $this->assertSame('ug-berlin', $config->getArgument(1)['key']);
        $this->assertSame(['web-php'], $config->getArgument(1)['categories']);

        $built = SourceConfig::fromArray('jsonld', $jsonld->getArgument(0)->getArgument(1));
        $this->assertSame('jsonld.conf', $built->getSourceKey());
        $this->assertSame(120, $built->getPriority());
        $this->assertSame('en', $built->locale);
    }

    public function testEnabledImportNeedsAUser(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('sulu_event.import.user is required');

        $this->load(['import' => ['enabled' => true]]);
    }

    public function testSourceNeedsAKeyAndAUrl(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('url');

        $this->load(['import' => ['sources' => ['ical' => [['key' => 'a']]]]]);
    }

    public function testSourceUrlMustBeHttp(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('is not an http(s) url');

        $this->load(['import' => ['sources' => ['ical' => [['key' => 'a', 'url' => 'file:///etc/passwd']]]]]);
    }

    public function testSourceKeyIsRestricted(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('may only contain lower case letters');

        $this->load(['import' => ['sources' => ['ical' => [['key' => 'My Group!', 'url' => 'https://example.com/a.ics']]]]]);
    }

    public function testSourceKeyMustBeUniquePerKind(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('used twice');

        $this->load(['import' => ['sources' => ['ical' => [
            ['key' => 'a', 'url' => 'https://example.com/a.ics'],
            ['key' => 'a', 'url' => 'https://example.com/b.ics'],
        ]]]]);
    }

    public function testSourceTypeMustBeAKnownEventType(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('"workshop-xyz" is not an event type');

        $this->load(['import' => ['sources' => ['jsonld' => [['key' => 'a', 'url' => 'https://example.com/', 'type' => 'workshop-xyz']]]]]);
    }

    public function testTimezoneOfASourceMustBeValid(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('is not a valid IANA timezone');

        $this->load(['import' => ['sources' => ['ical' => [['key' => 'a', 'url' => 'https://example.com/a.ics', 'timezone' => 'Mars/Olympus']]]]]);
    }

    public function testUrlPrefixMustStartWithASlash(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('must start with a slash');

        $this->load(['import' => ['url_prefix' => 'events']]);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function load(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new SuluEventExtension())->load([$config], $container);

        return $container;
    }
}
