<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\DependencyInjection;

use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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

        $container->setParameter(
            'sulu_event.routing.route_schema',
            $config['routing']['route_schema']
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('controller.yaml');
        $loader->load('services-calendar.yaml');
        $loader->load('services-ical.yaml');
        $loader->load('services-feed.yaml');

        $this->configurePersistence($config['objects'], $container);
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
                                'dir' => __DIR__ . '/../Resources/config/doctrine',
                                'prefix' => 'Manuxi\SuluEventBundle\Entity',
                                'alias' => 'SuluEventBundle',
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('jms_serializer')) {
            $container->prependExtensionConfig(
                'jms_serializer',
                [
                    'metadata' => [
                        'directories' => [
                            'SuluEventBundle' => [
                                'path' => __DIR__ . '/../Resources/config/serializer',
                                'namespace_prefix' => 'Manuxi\SuluEventBundle\Entity',
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
                $defaultConfigFile = __DIR__ . '/../Resources/config/packages/sulu_event.yaml';
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
                            __DIR__ . '/../Resources/config/lists',
                        ],
                    ],
                    'forms' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/forms',
                        ],
                    ],
                    'templates' => [
                        Event::TEMPLATE_TYPE => [
                            'default_type' => Event::TEMPLATE_TYPE,
                            'directories' => [
                                __DIR__ . '/../Resources/config/templates',
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
                                        'list_key' => Event::LIST_KEY,
                                        'display_properties' => [
                                            'title',
                                        ],
                                        'icon' => 'su-calendar',
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
                                        'list_key' => Event::LIST_KEY,
                                        'display_properties' => [
                                            'title',
                                        ],
                                        'icon' => 'su-calendar',
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
            'translator' => ['paths' => [__DIR__ . '/../Resources/translations/']],
        ]);
    }
}
