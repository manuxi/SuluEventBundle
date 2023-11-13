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
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SuluEventExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('controller.xml');
        $loader->load('automation.xml');

        $this->configurePersistence($config['objects'], $container);
    }

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('sulu_search')) {
            $container->prependExtensionConfig(
                'sulu_search',
                [
                    'indexes' => [
                        'event' => [
                            'name' => 'sulu_event.search_name',
                            'icon' => 'su-calendar',
                            'security_context' => Event::SECURITY_CONTEXT,
                            'view' => [
                                'name' => EventAdmin::EDIT_FORM_VIEW,
                                'result_to_view' => [
                                    'id' => 'id',
                                    'locale' => 'locale',
                                ],
                            ],

                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_route')) {
            $container->prependExtensionConfig(
                'sulu_route',
                [
                    'mappings' => [
                        Event::class => [
                            'generator' => 'schema',
                            'options' => [
                                //@TODO: works not yet as expected, does not translate correctly
                                //see https://github.com/sulu/sulu/pull/5920
                                'route_schema' => '/{translator.trans("sulu_event.events")}/{implode("-", object)}'
                            ],
                            'resource_key' => Event::RESOURCE_KEY,
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
                                        'id' => 'id'
                                    ]
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Event::LIST_KEY,
                                        'display_properties' => [
                                            'title'
                                        ],
                                        'icon' => 'su-calendar',
                                        'label' => 'sulu_event.event_selection_label',
                                        'overlay_title' => 'sulu_event.select_events'
                                    ]
                                ]
                            ]
                        ],
                        'single_selection' => [
                            'single_event_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Event::RESOURCE_KEY,
                                'view' => [
                                    'name' => EventAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id'
                                    ]
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Event::LIST_KEY,
                                        'display_properties' => [
                                            'title'
                                        ],
                                        'icon' => 'su-calendar',
                                        'empty_text' => 'sulu_event.no_event_selected',
                                        'overlay_title' => 'sulu_event.select_event'
                                    ],
                                    'auto_complete' => [
                                        'display_property' => 'title',
                                        'search_properties' => [
                                            'title'
                                        ]
                                    ]
                                ]
                            ],
                            'single_location_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Location::RESOURCE_KEY,
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Location::LIST_KEY,
                                        'display_properties' => [
                                            'name'
                                        ],
                                        'icon' => 'fa-home',
                                        'empty_text' => 'sulu_event.no_location_selected',
                                        'overlay_title' => 'sulu_event.select_location'
                                    ],
                                    'auto_complete' => [
                                        'display_property' => 'name',
                                        'search_properties' => [
                                            'name'
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ],
                ]
            );
        }

        $container->loadFromExtension('framework', [
            'default_locale' => 'en',
            'translator' => ['paths' => [__DIR__ . '/../Resources/config/translations/']],
        ]);
    }
}
