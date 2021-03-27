<?php

namespace Manuxi\SuluEventBundle\DependencyInjection;

use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Entity\Event;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SuluEventExtension extends Extension implements PrependExtensionInterface
{

    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('controller.xml');


//        $this->configurePersistence($config['objects'], $container);
    }

    public function prepend(ContainerBuilder $container)
    {
//        if ($container->hasExtension('sulu_search')) {
//            $container->prependExtensionConfig(
//                'sulu_search',
//                [
//                    'indexes' => [
//                        'contact' => [
//                            'name' => 'Event',
//                            'icon' => 'su-calendar',
//                            'view' => [
//                                'name' => EventAdmin::EDIT_FORM_VIEW,
//                                'result_to_view' => [
//                                    'id' => 'id',
//                                    'locale' => 'locale',
//                                ],
//                            ],
//                            'security_context' => EventAdmin::SECURITY_CONTEXT,
//                        ],
//                    ],
//                ]
//            );
//        }

        if ($container->hasExtension('sulu_route')) {
            $container->prependExtensionConfig(
                'sulu_route',
                [
                    'mappings' => [
                        Event::class => [
                            'generator' => 'schema',
                            'options' => [
                                'route_schema' => '/events/{implode("-", object)}'
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
                                'list' => 'app.get_events',
                                'detail' => 'app.get_event',
                            ],
                        ],
                        'locations' => [
                            'routes' => [
                                'list' => 'app.get_locations',
                                'detail' => 'app.get_location',
                            ],
                        ],
                    ],
                    'field_type_options' => [
                        'selection' => [
                            'event_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => 'events',
                                'view' => [
                                    'name' => 'app.event_edit_form',
                                    'result_to_view' => [
                                        'id' => 'id'
                                    ]
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => 'events',
                                        'display_properties' => [
                                            'title'
                                        ],
                                        'icon' => 'su-calendar',
                                        'label' => 'app.events',
                                        'overlay_title' => 'app.events'
                                    ]
                                ]
                            ]
                        ],
                        'single_selection' => [
                            'single_event_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => 'events',
                                'view' => [
                                    'name' => 'app.event_edit_form',
                                    'result_to_view' => [
                                        'id' => 'id'
                                    ]
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => 'events',
                                        'display_properties' => [
                                            'title'
                                        ],
                                        'icon' => 'su-calendar',
                                        'empty_text' => 'app.events.no_selections',
                                        'overlay_title' => 'app.events'
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
                                'resource_key' => 'locations',
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => 'locations',
                                        'display_properties' => [
                                            'name'
                                        ],
                                        'icon' => 'fa-home',
                                        'empty_text' => 'app.location.no_selections',
                                        'overlay_title' => 'app.locations'
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

//        $container->prependExtensionConfig(
//            'sulu_event',
//            ['templates' => ['view' => 'event/index.html.twig']]
//        );

        $container->loadFromExtension('framework', [
            'default_locale' => 'en',
            'translator' => ['paths' => [__DIR__ . '/../Resources/config/translations/']],
            // ...
        ]);
    }
}
