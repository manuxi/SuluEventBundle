<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Admin;

use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\View\ActivityViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\DropdownToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\TogglerToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class EventAdmin extends Admin
{
    public const NAV_ITEM = 'sulu_event.events';

    public const LIST_VIEW = 'sulu_event.event.list';
    public const ADD_FORM_VIEW = 'sulu_event.event.add_form';
    public const ADD_FORM_DETAILS_VIEW = 'sulu_event.event.add_form.details';
    public const EDIT_FORM_VIEW = 'sulu_event.event.edit_form';
    public const EDIT_FORM_DETAILS_VIEW = 'sulu_event.event.edit_form.details';
    public const SECURITY_CONTEXT = 'sulu.modules.events';

    public const EDIT_FORM_VIEW_SEO = 'sulu_event.edit_form.seo';
    public const EDIT_FORM_VIEW_EXCERPT = 'sulu_event.edit_form.excerpt';
    public const EDIT_FORM_VIEW_SETTINGS = 'sulu_event.event.edit_form.settings';
    public const EDIT_FORM_VIEW_RECURRENCE = 'sulu_event.event.edit_form.recurrence';
    public const EDIT_FORM_VIEW_ACTIVITY = 'sulu_event.event.edit_form.activity';
    public const EDIT_FORM_VIEW_SOCIAL = 'sulu_event.event.edit_form.social';

    public function __construct(
        private ViewBuilderFactoryInterface $viewBuilderFactory,
        private SecurityCheckerInterface $securityChecker,
        private WebspaceManagerInterface $webspaceManager,
        private ActivityViewBuilderFactoryInterface $activityViewBuilderFactory,
    ) {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $rootNavigationItem = new NavigationItem(static::NAV_ITEM);
            $rootNavigationItem->setIcon('su-calendar');
            $rootNavigationItem->setPosition(37);
            $rootNavigationItem->setView(static::LIST_VIEW);

            // Configure a NavigationItem with a View
            $eventNavigationItem = new NavigationItem(static::NAV_ITEM);
            $eventNavigationItem->setPosition(10);
            $eventNavigationItem->setView(static::LIST_VIEW);

            $rootNavigationItem->addChild($eventNavigationItem);

            $navigationItemCollection->add($rootNavigationItem);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $locales = $this->webspaceManager->getAllLocales();

        $formToolbarActions = [
            new ToolbarAction('sulu_admin.save'),
            new ToolbarAction('sulu_admin.delete'),
            new TogglerToolbarAction(
                'sulu_event.enable_event',
                'enabled',
                'enable',
                'disable'
            ),
            new DropdownToolbarAction(
                'sulu_admin.edit',
                'su-pen',
                [
                    new ToolbarAction('sulu_admin.delete'),
                    new TogglerToolbarAction(
                        'sulu_event.enable_event',
                        'enabled',
                        'enable',
                        'disable'
                    ),
                ]
            ),
        ];

        $listToolbarActions = [
            new ToolbarAction('sulu_admin.add'),
            new ToolbarAction('sulu_admin.delete'),
        ];

        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $viewCollection->add(
                $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, '/events/:locale')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setListKey('events')
                    ->setTitle('sulu_event.events')
                    ->addListAdapters(['table'])
                    ->addLocales($locales)
                    ->setDefaultLocale($locales[0])
                    ->setAddView(static::ADD_FORM_VIEW)
                    ->setEditView(static::EDIT_FORM_VIEW)
                    ->addToolbarActions($listToolbarActions)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::ADD_FORM_VIEW, '/events/:locale/add')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::ADD_FORM_DETAILS_VIEW, '/details')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('event_details')
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::EDIT_FORM_VIEW)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::ADD_FORM_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, '/events/:locale/:id')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
                    ->setTitleProperty('title')
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createPreviewFormViewBuilder(static::EDIT_FORM_DETAILS_VIEW, '/details')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('event_details')
                    ->setTabTitle('sulu_admin.details')
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createFormViewBuilder(static::EDIT_FORM_VIEW_SETTINGS, '/settings')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('event_settings')
                    ->setTabTitle('sulu_event.settings.title')
                    ->setTabOrder(1024)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createFormViewBuilder(static::EDIT_FORM_VIEW_SEO, '/seo')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('content_seo_metadata')
                    ->setTabTitle('sulu_content.seo')
                    ->setTabOrder(2048)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createFormViewBuilder(static::EDIT_FORM_VIEW_EXCERPT, '/excerpt')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('content_excerpt_metadata')
                    ->setTabTitle('sulu_content.excerpt')
                    ->setTabOrder(3072)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createFormViewBuilder(static::EDIT_FORM_VIEW_SOCIAL, '/social')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('event_settings_social')
                    ->setTabTitle('sulu_event.social_media.title')
                    ->setTabOrder(3584)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createFormViewBuilder(static::EDIT_FORM_VIEW_RECURRENCE, '/recurrence')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('event_recurrence')
                    ->setTabTitle('sulu_event.recurrence.title')
                    ->setTabOrder(4096)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            if ($this->activityViewBuilderFactory->hasActivityListPermission()) {
                $insightsResourceTabViewName = static::EDIT_FORM_VIEW . '.insights';

                $viewCollection->add(
                    $this->viewBuilderFactory
                        ->createResourceTabViewBuilder($insightsResourceTabViewName, '/insights')
                        ->setResourceKey(Event::RESOURCE_KEY)
                        ->setTabOrder(6144)
                        ->setTabTitle('sulu_admin.insights')
                        ->setTitleProperty('')
                        ->setParent(static::EDIT_FORM_VIEW)
                );

                $viewCollection->add(
                    $this->activityViewBuilderFactory
                        ->createActivityListViewBuilder(
                            $insightsResourceTabViewName . '.activity',
                            '/activity',
                            Event::RESOURCE_KEY
                        )
                        ->setParent($insightsResourceTabViewName)
                );

                $viewCollection->add(
                    $this->viewBuilderFactory
                        ->createListViewBuilder(
                            $insightsResourceTabViewName . '.versions',
                            '/versions',
                            'events_versions'
                        )
                        ->setResourceKey(Event::RESOURCE_KEY)
                        ->setListKey('events_versions')
                        ->setTabTitle('sulu_admin.versions')
                        ->addListAdapters(['table'])
                        ->disableSelection()
                        ->addAdapterOptions(['table' => ['show_header' => true]])
                        ->setParent($insightsResourceTabViewName)
                );
            }
        }
    }

    /**
     * @return mixed[]
     */
    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Events' => [
                    Event::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                        PermissionTypes::LIVE,
                    ],
                ],
            ],
        ];
    }

    public function getConfigKey(): ?string
    {
        return 'sulu_event';
    }
}