<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\DependencyInjection;

use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Import\EventSourceInterface;
use Manuxi\SuluEventBundle\Import\Source\ICalSource;
use Manuxi\SuluEventBundle\Import\Source\JsonLdSource;
use Manuxi\SuluEventBundle\Import\SourceConfig;
use Manuxi\SuluEventBundle\Repository\EventDimensionContentRepository;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class SuluEventExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sulu_event.types', $config['types'] ?? []);
        $container->setParameter('sulu_event.default_type', $config['default_type'] ?? 'default');

        $container->setParameter('sulu_event.list_date_format', $config['list_date_format']);
        $container->setParameter('sulu_event.timezone', $config['timezone']);
        $container->setParameter('sulu_event.category_root', $config['category_root']);
        $container->setParameter('sulu_event.sitemap.hide_past_after_days', $config['sitemap']['hide_past_after_days']);

        $container->setParameter(
            'sulu_event.routing.route_schema',
            $config['routing']['route_schema']
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('controller.yaml');
        $loader->load('services-calendar.yaml');
        $loader->load('services-ical.yaml');
        $loader->load('services-feed.yaml');
        $loader->load('services-import.yaml');
        $this->configureImport($config, $container);

        // tools for the Sulu MCP server, only if sulu/mcp-bundle is installed
        if (class_exists(\Mcp\Capability\Attribute\McpTool::class) && class_exists(\Sulu\Mcp\Domain\Security\RequiresPermission::class)) {
            $loader->load('services-mcp.yaml');
        }

        // scheduled publish/unpublish tasks, only if sulu/automation-bundle is installed
        if (interface_exists(\Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface::class)) {
            $loader->load('services-automation.yaml');
        }

        $this->configurePersistence($config['objects'], $container);

        // PersistenceExtensionTrait::configurePersistence() unconditionally generates
        // "sulu.repository.*" service definitions with a (EntityManager, ClassMetadata)
        // constructor signature (the default for Sulu's own EntityRepository base class).
        // Our repositories extend Doctrine's ServiceEntityRepository, which requires a
        // ManagerRegistry instead, so those generated definitions are invalid and make
        // `bin/console lint:container` fail. The repositories are already correctly wired
        // as public FQCN services above, so replace the broken generated definitions with
        // aliases to them. This must happen after configurePersistence(), since that call
        // would otherwise overwrite an alias defined earlier (e.g. in services.yaml).
        $container->setAlias('sulu.repository.event', EventRepository::class)->setPublic(true);
        $container->setAlias('sulu.repository.event_dimension_content', EventDimensionContentRepository::class)->setPublic(true);
        $container->setAlias('sulu.repository.location', LocationRepository::class)->setPublic(true);
    }

    /**
     * Parameters of the import and one service per configured source (tagged "sulu_event.import_source", like the
     * sources projects add themselves).
     *
     * @param array<string, mixed> $config
     */
    private function configureImport(array $config, ContainerBuilder $container): void
    {
        $import = $config['import'];

        foreach (['enabled', 'user', 'user_agent', 'timeout', 'auto_update_published_dates', 'url_prefix', 'webspace'] as $key) {
            $container->setParameter('sulu_event.import.' . $key, $import[$key]);
        }

        $container->registerForAutoconfiguration(EventSourceInterface::class)->addTag('sulu_event.import_source');

        $types    = array_keys($config['types'] ?? []);
        $usedKeys = [];

        foreach ([SourceConfig::KIND_ICAL => ICalSource::class, SourceConfig::KIND_JSONLD => JsonLdSource::class] as $kind => $class) {
            foreach ($import['sources'][$kind] as $index => $source) {
                $path = \sprintf('sulu_event.import.sources.%s[%d] ("%s")', $kind, $index, $source['key']);

                if (isset($usedKeys[$kind . '.' . $source['key']])) {
                    throw new InvalidConfigurationException(\sprintf('Invalid configuration for path "%s": the key "%s" is used twice for %s sources. Every source needs its own key.', $path, $source['key'], $kind));
                }
                $usedKeys[$kind . '.' . $source['key']] = true;

                if (null !== $source['type'] && !\in_array($source['type'], $types, true)) {
                    throw new InvalidConfigurationException(\sprintf('Invalid configuration for path "%s.type": "%s" is not an event type. Known types: %s (see sulu_event.types).', $path, $source['type'], implode(', ', $types)));
                }

                $sourceConfig = (new Definition(SourceConfig::class, [$kind, $source]))
                    ->setFactory([SourceConfig::class, 'fromArray']);

                $definition = new Definition($class, [
                    $sourceConfig,
                    new Reference('sulu_event.import.http_client'),
                    new Reference('sulu_event.import.timezone_guesser'),
                ]);
                $definition->addTag('sulu_event.import_source');

                $container->setDefinition('sulu_event.import.source.' . $kind . '.' . $source['key'], $definition);
            }
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('doctrine')) {
            $container->prependExtensionConfig(
                'doctrine',
                [
                    'orm' => [
                        'mappings' => [
                            'SuluEventBundle' => [
                                'type' => 'xml',
                                'dir' => __DIR__.'/../Resources/config/doctrine',
                                'prefix' => 'Manuxi\SuluEventBundle\Entity',
                                'alias' => 'SuluEventBundle',
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_event')) {
            // Load all existing configs to check if project has defined types
            $configs = $container->getExtensionConfig('sulu_event');

            // Check if any config already defines types
            $hasProjectTypes = false;
            foreach ($configs as $config) {
                if (isset($config['types'])) {
                    $hasProjectTypes = true;
                    break;
                }
            }

            // Only prepend bundle defaults if project hasn't defined types
            // This allows projects to completely replace default types
            if (!$hasProjectTypes) {
                $defaultConfigFile = __DIR__.'/../Resources/config/packages/sulu_event.yaml';
                $defaultConfig = Yaml::parseFile($defaultConfigFile);

                if (isset($defaultConfig['sulu_event'])) {
                    $container->prependExtensionConfig('sulu_event', $defaultConfig['sulu_event']);
                }
            }
        }

        if ($container->hasExtension('sulu_search')) {
            $container->prependExtensionConfig(
                'sulu_search',
                [
                    'admin' => [
                        'resources' => [
                            Event::RESOURCE_KEY => [
                                'name' => 'sulu_event.events',
                                'icon' => 'su-calendar',
                                'route' => [
                                    'name' => EventAdmin::EDIT_FORM_VIEW,
                                    'resultToRoute' => [
                                        'resourceId' => 'id',
                                        'locale' => 'locale',
                                    ],
                                ],
                                'securityContext' => Event::SECURITY_CONTEXT,
                            ],
                        ],
                    ],
                ],
            );
        }

        if ($container->hasExtension('sulu_seo')) {
            $container->prependExtensionConfig(
                'sulu_seo',
                [
                    'content' => [
                        'types' => [
                            Event::TEMPLATE_TYPE => [
                                'template_driver' => true,
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_excerpt')) {
            $container->prependExtensionConfig(
                'sulu_excerpt',
                [
                    'content' => [
                        'types' => [
                            Event::TEMPLATE_TYPE => [
                                'template_driver' => true,
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_media')) {
            $container->prependExtensionConfig(
                'sulu_media',
                [
                    'system_collections' => [
                        'sulu_event' => [
                            'meta_title' => ['en' => 'Events', 'de' => 'Veranstaltungen'],
                            'collections' => [
                                'events' => [
                                    'meta_title' => ['en' => 'Events', 'de' => 'Veranstaltungen'],
                                ],
                                'locations' => [
                                    'meta_title' => ['en' => 'Locations', 'de' => 'Veranstaltungsorte'],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_admin')) {
            $container->prependExtensionConfig(
                'sulu_admin',
                [
                    'lists' => [
                        'directories' => [
                            __DIR__.'/../Resources/config/lists',
                        ],
                    ],
                    'forms' => [
                        'directories' => [
                            __DIR__.'/../Resources/config/forms',
                        ],
                    ],
                    'templates' => [
                        Event::TEMPLATE_TYPE => [
                            'default_type' => Event::TEMPLATE_TYPE,
                            'directories' => [
                                'bundle' => __DIR__.'/../Resources/config/templates/events',
                                'app' => '%kernel.project_dir%/config/templates/events',
                            ],
                        ],
                    ],
                    'resources' => [
                        'events' => [
                            'routes' => [
                                'list' => 'sulu_event.get_events',
                                'detail' => 'sulu_event.get_event',
                            ],
                        ],
                        'events_versions' => [
                            'routes' => [
                                'list' => 'sulu_event.get_event_versions',
                                'detail' => 'sulu_event.get_event',
                            ],
                        ],
                        'locations' => [
                            'routes' => [
                                'list' => 'sulu_event.get_locations',
                                'detail' => 'sulu_event.get_location',
                            ],
                        ],
                        'event-settings' => [
                            'routes' => [
                                'detail' => 'sulu_event.get_event-settings',
                            ],
                        ],
                    ],
                    'field_type_options' => [
                        'selection' => [
                            'event_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Event::RESOURCE_KEY,
                                'view' => [
                                    'name' => EventAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Event::LIST_KEY_PUBLISHED,
                                        'display_properties' => [
                                            'title',
                                            'date',
                                        ],
                                        'icon' => 'su-filter',
                                        'label' => 'sulu_event.event_selection_label',
                                        'overlay_title' => 'sulu_event.select_events',
                                    ],
                                ],
                            ],
                        ],
                        'single_selection' => [
                            'single_event_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Event::RESOURCE_KEY,
                                'view' => [
                                    'name' => EventAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Event::LIST_KEY_PUBLISHED,
                                        'display_properties' => [
                                            'title',
                                            'date'
                                        ],
                                        'icon' => 'su-filter',
                                        'empty_text' => 'sulu_event.no_event_selected',
                                        'overlay_title' => 'sulu_event.select_event',
                                    ],
                                    'auto_complete' => [
                                        'display_property' => 'title',
                                        'search_properties' => [
                                            'title',
                                        ],
                                    ],
                                ],
                            ],
                            'single_location_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Location::RESOURCE_KEY,
                                'view' => [
                                    'name' => 'sulu_event.location.edit_form',
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Location::LIST_KEY,
                                        'display_properties' => [
                                            'name',
                                        ],
                                        'icon' => 'fa-home',
                                        'empty_text' => 'sulu_event.no_location_selected',
                                        'overlay_title' => 'sulu_event.select_location',
                                    ],
                                    'auto_complete' => [
                                        'display_property' => 'name',
                                        'search_properties' => [
                                            'name',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('framework')) {
            $container->prependExtensionConfig(
                'framework',
                [
                    'rate_limiter' => [
                        'sulu_event_calendar_api' => [
                            'policy' => 'sliding_window',
                            'limit' => 100,
                            'interval' => '1 minute',
                        ],
                    ],
                ]
            );
        }

        $container->loadFromExtension('framework', [
            'default_locale' => 'en',
            'translator' => ['paths' => [__DIR__.'/../Resources/translations/']],
        ]);
    }
}
