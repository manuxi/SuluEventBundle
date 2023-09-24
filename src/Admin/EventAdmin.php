<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Admin;

use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\TogglerToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\AutomationBundle\Admin\AutomationAdmin;
use Sulu\Bundle\AutomationBundle\Admin\View\AutomationViewBuilderFactoryInterface;
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

    //seo,excerpt, etc
    public const EDIT_FORM_VIEW_SEO = 'sulu_event.event.edit_form.seo';
    public const EDIT_FORM_VIEW_EXCERPT = 'sulu_event.event.edit_form.excerpt';
    public const EDIT_FORM_VIEW_SETTINGS = 'sulu_event.event.edit_form.settings';
    public const EDIT_FORM_VIEW_AUTOMATION = 'sulu_event.event.edit_form.automation';

    private ViewBuilderFactoryInterface $viewBuilderFactory;
    private SecurityCheckerInterface $securityChecker;
    private WebspaceManagerInterface $webspaceManager;
    private ?AutomationViewBuilderFactoryInterface $automationViewBuilderFactory;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        SecurityCheckerInterface $securityChecker,
        WebspaceManagerInterface $webspaceManager,
        ?AutomationViewBuilderFactoryInterface $automationViewBuilderFactory
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker    = $securityChecker;
        $this->webspaceManager    = $webspaceManager;
        $this->automationViewBuilderFactory = $automationViewBuilderFactory;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $rootNavigationItem = new NavigationItem(static::NAV_ITEM);
            $rootNavigationItem->setIcon('su-calendar');
            $rootNavigationItem->setPosition(30);
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
        $formToolbarActions = [];
        $listToolbarActions = [];

        $locales = $this->webspaceManager->getAllLocales();

        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }

        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save');
        }

        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.delete');
            $listToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }

        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.export');
        }

        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            // Configure Event List View
            $listView = $this->viewBuilderFactory
                ->createListViewBuilder(static::LIST_VIEW, '/events/:locale')
                ->setResourceKey(Event::RESOURCE_KEY)
                ->setListKey(Event::LIST_KEY)
                ->setTitle('sulu_event.events')
                ->addListAdapters(['table'])
                ->addLocales($locales)
                ->setDefaultLocale($locales[0])
                ->setAddView(static::ADD_FORM_VIEW)
                ->setEditView(static::EDIT_FORM_VIEW)
                ->addToolbarActions($listToolbarActions);
            $viewCollection->add($listView);

            // Configure Event Add View
            $addFormView = $this->viewBuilderFactory
                ->createResourceTabViewBuilder(static::ADD_FORM_VIEW, '/events/:locale/add')
                ->setResourceKey(Event::RESOURCE_KEY)
                ->setBackView(static::LIST_VIEW)
                ->addLocales($locales);
            $viewCollection->add($addFormView);

            $addDetailsFormView = $this->viewBuilderFactory
                ->createFormViewBuilder(static::ADD_FORM_DETAILS_VIEW, '/details')
                ->setResourceKey(Event::RESOURCE_KEY)
                ->setFormKey(Event::FORM_KEY)
                ->setTabTitle('sulu_admin.details')
                ->setEditView(static::EDIT_FORM_VIEW)
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::ADD_FORM_VIEW);
            $viewCollection->add($addDetailsFormView);

            // Configure Event Edit View
            $editFormView = $this->viewBuilderFactory
                ->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, '/events/:locale/:id')
                ->setResourceKey(Event::RESOURCE_KEY)
                ->setBackView(static::LIST_VIEW)
                ->setTitleProperty('title')
                ->addLocales($locales);
            $viewCollection->add($editFormView);

            //enable/disable toolbar actions
            $formToolbarActions = [
                new ToolbarAction('sulu_admin.save'),
                new ToolbarAction('sulu_admin.delete'),
                new TogglerToolbarAction(
                    'sulu_event.enable_event',
                    'enabled',
                    'enable',
                    'disable'
                ),
            ];

            $editDetailsFormView = $this->viewBuilderFactory
                ->createPreviewFormViewBuilder(static::EDIT_FORM_DETAILS_VIEW, '/details')
                ->setPreviewCondition('id != null')
                ->setResourceKey(Event::RESOURCE_KEY)
                ->setFormKey(Event::FORM_KEY)
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::EDIT_FORM_VIEW);
            $viewCollection->add($editDetailsFormView);

            //seo,excerpt, etc
            $formToolbarActionsWithoutType = [];
            $previewCondition              = 'nodeType == 1';

            if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::ADD)) {
                $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
            }

            $formToolbarActionsWithoutType[] = new ToolbarAction('sulu_admin.save');

            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createPreviewFormViewBuilder(static::EDIT_FORM_VIEW_SEO, '/seo')
//                    ->disablePreviewWebspaceChooser()
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('page_seo')
                    ->setTabTitle('sulu_page.seo')
//                    ->setTabCondition('nodeType == 1 && shadowOn == false')
                    ->addToolbarActions($formToolbarActionsWithoutType)
//                    ->addRouterAttributesToFormRequest($routerAttributesToFormRequest)
                    ->setPreviewCondition($previewCondition)
                    ->setTitleVisible(true)
                    ->setTabOrder(2048)
                    ->setParent(static::EDIT_FORM_VIEW)
            );
            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createPreviewFormViewBuilder(static::EDIT_FORM_VIEW_EXCERPT, '/excerpt')
//                    ->disablePreviewWebspaceChooser()
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('page_excerpt')
                    ->setTabTitle('sulu_page.excerpt')
//                    ->setTabCondition('(nodeType == 1 || nodeType == 4) && shadowOn == false')
                    ->addToolbarActions($formToolbarActionsWithoutType)
//                    ->addRouterAttributesToFormRequest($routerAttributesToFormRequest)
//                    ->addRouterAttributesToFormMetadata($routerAttributesToFormMetadata)
                    ->setPreviewCondition($previewCondition)
                    ->setTitleVisible(true)
                    ->setTabOrder(3072)
                    ->setParent(static::EDIT_FORM_VIEW)
            );
            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createPreviewFormViewBuilder(static::EDIT_FORM_VIEW_SETTINGS, '/settings')
                    ->disablePreviewWebspaceChooser()
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('event_settings')
                    ->setTabTitle('sulu_page.settings')
                    ->addToolbarActions($formToolbarActionsWithoutType)
                    ->setPreviewCondition($previewCondition)
                    ->setTitleVisible(true)
                    ->setTabOrder(4096)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            if ($this->automationViewBuilderFactory
                && $this->securityChecker->hasPermission(AutomationAdmin::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ) {
                $viewCollection->add(
                    $this->automationViewBuilderFactory
                        ->createTaskListViewBuilder(static::EDIT_FORM_VIEW_AUTOMATION,'/automation',Event::class)
                        ->setTabOrder(5120)
                        ->setParent(static::EDIT_FORM_VIEW)
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
