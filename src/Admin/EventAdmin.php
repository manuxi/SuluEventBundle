<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Admin;

use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\View\ActivityViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\DropdownToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Localization\Manager\LocalizationManagerInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Content\Infrastructure\Sulu\Admin\ContentViewBuilderFactoryInterface;

class EventAdmin extends Admin
{
    public const NAV_ITEM = 'sulu_event.events';

    public const LIST_VIEW = 'sulu_event.event.list';
    public const ADD_TABS_VIEW = 'sulu_event.event.add_tabs';
    public const EDIT_TABS_VIEW = 'sulu_event.event.edit_tabs';

    // Backward compatibility - used in SuluEventExtension
    public const EDIT_FORM_VIEW = self::EDIT_TABS_VIEW;

    public const EDIT_FORM_DETAILS_VIEW = 'sulu_event.event.edit_form.details';

    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly ContentViewBuilderFactoryInterface $contentViewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
        private readonly LocalizationManagerInterface $localizationManager,
        private readonly ActivityViewBuilderFactoryInterface $activityViewBuilderFactory,
    ) {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $rootNavigationItem = new NavigationItem(static::NAV_ITEM);
            $rootNavigationItem->setIcon('su-calendar');
            $rootNavigationItem->setPosition(37);
            $rootNavigationItem->setView(static::LIST_VIEW);

            $eventNavigationItem = new NavigationItem(static::NAV_ITEM);
            $eventNavigationItem->setPosition(10);
            $eventNavigationItem->setView(static::LIST_VIEW);

            $rootNavigationItem->addChild($eventNavigationItem);

            $navigationItemCollection->add($rootNavigationItem);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $locales = $this->localizationManager->getLocales();
        $resourceKey = Event::RESOURCE_KEY;

        $formToolbarActions = [];
        $listToolbarActions = [];

        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }

        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::LIVE)) {
            $editDropdownToolbarActions = [
                new ToolbarAction('sulu_admin.save'),
                new ToolbarAction('sulu_admin.publish'),
                new ToolbarAction('sulu_admin.set_unpublished'),
            ];

            if (\count($locales) > 1) {
                $editDropdownToolbarActions[] = new ToolbarAction('sulu_admin.copy_locale');
            }

            $formToolbarActions[] = new DropdownToolbarAction(
                'sulu_admin.edit',
                'su-cog',
                $editDropdownToolbarActions
            );
        } elseif ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save');
        }

        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            // List View
            $viewCollection->add(
                $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, '/'.$resourceKey.'/:locale')
                    ->setResourceKey($resourceKey)
                    ->setListKey($resourceKey)
                    ->setTitle('sulu_event.'.$resourceKey)
                    ->addListAdapters(['table'])
                    ->addLocales($locales)
                    ->setDefaultLocale($locales[0])
                    ->setAddView(static::ADD_TABS_VIEW)
                    ->setEditView(static::EDIT_TABS_VIEW)
                    ->addToolbarActions($listToolbarActions)
            );

            // Add Tabs View
            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::ADD_TABS_VIEW, '/'.$resourceKey.'/:locale/add')
                    ->setResourceKey($resourceKey)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
            );

            // Edit Tabs View
            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::EDIT_TABS_VIEW, '/'.$resourceKey.'/:locale/:id')
                    ->setResourceKey($resourceKey)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
                    ->setTitleProperty('title')
            );

            // Content Views (Details, SEO, Excerpt)
            $viewBuilders = $this->contentViewBuilderFactory->createViews(
                Event::class,
                static::EDIT_TABS_VIEW,
                static::ADD_TABS_VIEW,
                Event::SECURITY_CONTEXT,
                []
            );

            foreach ($viewBuilders as $viewBuilder) {
                // Ensure toolbar actions are set (Workaround for missing Save button on SEO/Excerpt)
                if (method_exists($viewBuilder, 'addToolbarActions') && $viewBuilder->getName() === static::EDIT_FORM_DETAILS_VIEW) {
                    $viewBuilder->addToolbarActions($formToolbarActions);
                }
                $viewCollection->add($viewBuilder);
            }

            // Settings Tab
            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createFormViewBuilder(static::EDIT_TABS_VIEW.'.settings', '/settings')
                    ->setResourceKey($resourceKey)
                    ->setFormKey('event_settings')
                    ->setTabTitle('sulu_event.settings.title')
                    ->setTabOrder(1024)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_TABS_VIEW)
            );

            // Social Media Tab
            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createFormViewBuilder(static::EDIT_TABS_VIEW.'.social', '/social')
                    ->setResourceKey($resourceKey)
                    ->setFormKey('event_settings_social')
                    ->setTabTitle('sulu_event.social_media.title')
                    ->setTabOrder(3584)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_TABS_VIEW)
            );

            // Recurrence Tab
            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createFormViewBuilder(static::EDIT_TABS_VIEW.'.recurrence', '/recurrence')
                    ->setResourceKey($resourceKey)
                    ->setFormKey('event_recurrence')
                    ->setTabTitle('sulu_event.recurrence.title')
                    ->setTabOrder(4096)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_TABS_VIEW)
            );

            // Activity/Insights Tab
            if ($this->activityViewBuilderFactory->hasActivityListPermission()) {
                $insightsResourceTabViewName = static::EDIT_TABS_VIEW.'.insights';

                $viewCollection->add(
                    $this->viewBuilderFactory
                        ->createResourceTabViewBuilder($insightsResourceTabViewName, '/insights')
                        ->setResourceKey($resourceKey)
                        ->setTabOrder(6144)
                        ->setTabTitle('sulu_admin.insights')
                        ->setTitleProperty('')
                        ->setParent(static::EDIT_TABS_VIEW)
                );

                $viewCollection->add(
                    $this->activityViewBuilderFactory
                        ->createActivityListViewBuilder(
                            $insightsResourceTabViewName.'.activity',
                            '/activity',
                            Event::RESOURCE_KEY
                        )
                        ->setParent($insightsResourceTabViewName)
                );

                $viewCollection->add(
                    $this->viewBuilderFactory
                        ->createListViewBuilder(
                            $insightsResourceTabViewName.'.versions',
                            '/versions'
                        )
                        ->setResourceKey($resourceKey)
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
