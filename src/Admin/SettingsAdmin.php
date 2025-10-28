<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Admin;

use Manuxi\SuluEventBundle\Entity\EventSettings;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class SettingsAdmin extends Admin
{
    public const TAB_VIEW = 'sulu_event.config';
    public const FORM_VIEW = 'sulu_event.config.form';
    public const NAV_ITEM_TRANSLATION = 'sulu_event.config.title.navi';
    public const FORM_VIEW_SETTINGS_GENERAL = 'sulu_event.config.form.general';
    public const FORM_KEY_SETTINGS_GENERAL = 'event_settings_general';
    public const FORM_VIEW_SETTINGS_CALENDAR = 'sulu_event.config.form.calendar';
    public const FORM_KEY_SETTINGS_CALENDAR = 'event_settings_calendar';
    public const FORM_VIEW_SETTINGS_LISTS = 'sulu_event.config.form.lists';
    public const FORM_KEY_SETTINGS_LISTS = 'event_settings_lists';
    public const FORM_VIEW_SETTINGS_BREADCRUMBS = 'sulu_event.config.form.breadcrumbs';
    public const FORM_KEY_SETTINGS_BREADCRUMBS = 'event_settings_breadcrumbs';

    public function __construct(
        private ViewBuilderFactoryInterface $viewBuilderFactory,
        private SecurityCheckerInterface $securityChecker,
    ) {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(EventSettings::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $module = $navigationItemCollection->get(EventAdmin::NAV_ITEM);
            $settings = new NavigationItem(static::NAV_ITEM_TRANSLATION);
            $settings->setPosition(20);
            $settings->setView(static::TAB_VIEW);

            $module->addChild($settings);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if ($this->securityChecker->hasPermission(EventSettings::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $viewCollection->add(
                // sulu will only load the existing entity if the path of the form includes an id attribute
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::TAB_VIEW, '/event-settings/:id')
                    ->setResourceKey(EventSettings::RESOURCE_KEY)
                    ->setAttributeDefault('id', '-')
            );
/*
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::FORM_VIEW, '/config')
                    ->setResourceKey(EventSettings::RESOURCE_KEY)
                    ->setFormKey(EventSettings::FORM_KEY)
                    ->setTabTitle('sulu_event.config.tab.settings')
                    ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                    ->setParent(static::TAB_VIEW)
            );*/

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::FORM_VIEW_SETTINGS_GENERAL, '/'.static::FORM_KEY_SETTINGS_GENERAL)
                    ->setResourceKey(EventSettings::RESOURCE_KEY)
                    ->setFormKey(static::FORM_KEY_SETTINGS_GENERAL)
                    ->setTabTitle('sulu_event.config.tab.general')
                    ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                    ->setParent(static::TAB_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::FORM_VIEW_SETTINGS_CALENDAR, '/'.static::FORM_KEY_SETTINGS_CALENDAR)
                    ->setResourceKey(EventSettings::RESOURCE_KEY)
                    ->setFormKey(static::FORM_KEY_SETTINGS_CALENDAR)
                    ->setTabTitle('sulu_event.config.tab.calendar')
                    ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                    ->setParent(static::TAB_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::FORM_VIEW_SETTINGS_LISTS, '/'.static::FORM_KEY_SETTINGS_LISTS)
                    ->setResourceKey(EventSettings::RESOURCE_KEY)
                    ->setFormKey(static::FORM_KEY_SETTINGS_LISTS)
                    ->setTabTitle('sulu_event.config.tab.lists')
                    ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                    ->setParent(static::TAB_VIEW)
            );
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::FORM_VIEW_SETTINGS_BREADCRUMBS, '/'.static::FORM_KEY_SETTINGS_BREADCRUMBS)
                    ->setResourceKey(EventSettings::RESOURCE_KEY)
                    ->setFormKey(static::FORM_KEY_SETTINGS_BREADCRUMBS)
                    ->setTabTitle('sulu_event.config.tab.breadcrumbs')
                    ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                    ->setParent(static::TAB_VIEW)
            );
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
                    EventSettings::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::EDIT,
                    ],
                ],
            ],
        ];
    }

    public function getConfigKey(): ?string
    {
        return 'sulu_event.config.title';
    }
}
