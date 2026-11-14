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
    public function testLocationImageIsAnIdOrAFileName(): void
    {
        $this->assertNull($this->load([])->getParameter('sulu_event.import.location_image'));
        $this->assertSame('event-venue.jpg', $this->load(['import' => ['location_image' => 'event-venue.jpg']])->getParameter('sulu_event.import.location_image'));
        $this->assertSame(55, $this->load(['import' => ['location_image' => 55]])->getParameter('sulu_event.import.location_image'));

        $this->expectException(InvalidConfigurationException::class);
        $this->load(['import' => ['location_image' => ' ']]);
    }

    public function testCategoryKeysWithADashStayAsTheyAre(): void
    {
        $container = $this->load(['import' => ['category_images' => ['web-php' => 'event-topic-web-php.jpg', 'open-source' => 7]]]);

        $this->assertSame(['web-php' => 'event-topic-web-php.jpg', 'open-source' => 7], $container->getParameter('sulu_event.import.category_images'));
    }

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

    public function testCategoryImagesAreIdsOrFileNames(): void
    {
        $container = $this->load(['import' => ['category_images' => ['ai' => 123, 'games' => 'event-topic-games.jpg']]]);

        $this->assertSame(['ai' => 123, 'games' => 'event-topic-games.jpg'], $container->getParameter('sulu_event.import.category_images'));
    }

    public function testCategoryImageMustNotBeEmpty(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('id of a media or the name of its file');

        $this->load(['import' => ['category_images' => ['ai' => ' ']]]);
    }

    public function testUrlPrefixMustStartWithASlash(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('must start with a slash');

        $this->load(['import' => ['url_prefix' => 'events']]);
    }

    public function testTheSieveIsEmptyByDefault(): void
    {
        $container = $this->load([]);

        $this->assertSame([], $container->getParameter('sulu_event.import.filters'));
        $this->assertSame([], $container->getParameter('sulu_event.import.source_filters'));
        $this->assertTrue($container->hasDefinition('sulu_event.import.filter'));
    }

    public function testFiltersOfASourceAndOfTheProjectAreCollectedPerSourceKey(): void
    {
        $container = $this->load(['import' => [
            'filters'        => ['countries' => ['include' => ['de', 'at']], 'unknown_country' => 'deny'],
            'source_filters' => ['confs-tech' => ['max_months_ahead' => 12]],
            'sources'        => ['jsonld' => [
                ['key' => 'conf', 'url' => 'https://conf.example.com/', 'filters' => ['online' => 'deny']],
                ['key' => 'plain', 'url' => 'https://plain.example.com/'],
            ]],
        ]]);

        $this->assertSame(['countries' => ['include' => ['de', 'at']], 'unknown_country' => 'deny'], $container->getParameter('sulu_event.import.filters'));
        $this->assertSame(['confs-tech' => ['max_months_ahead' => 12], 'jsonld.conf' => ['online' => 'deny']], $container->getParameter('sulu_event.import.source_filters'));
    }

    /**
     * @dataProvider wrongFilters
     *
     * @param array<string, mixed> $import
     */
    public function testWrongRulesOfTheSieveAreRefusedWhenTheConfigurationIsLoaded(array $import, string $message): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($message);

        $this->load(['import' => $import]);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function wrongFilters(): iterable
    {
        yield 'country code' => [['filters' => ['countries' => ['include' => ['DE', 'XX']]]], '"XX" is not a country code'];
        yield 'regular expression' => [['filters' => ['title_exclude' => ['(open']]], 'not a valid regular expression'];
        yield 'unknown rule' => [['filters' => ['colour' => 'red']], 'Unrecognized option "colour"'];
        yield 'rule of a source' => [['sources' => ['ical' => [['key' => 'a', 'url' => 'https://a.example.com/a.ics', 'filters' => ['online' => 'sometimes']]]]], 'ical.a'];
        yield 'rule of a source by key' => [['source_filters' => ['confs-tech' => ['countries' => ['exclude' => ['ZZ']]]]], 'source_filters.confs-tech'];
        yield 'unknown type' => [['filters' => ['types' => ['party']]], '"party" is not an event type'];
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
