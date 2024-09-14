<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Admin;

use Manuxi\SuluEventBundle\Entity\Event;
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
    public const NAV_ITEM = 'sulu_event.config.title.navi';

    public function __construct(
        private ViewBuilderFactoryInterface $viewBuilderFactory,
        private SecurityCheckerInterface $securityChecker
    ) {}

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(EventSettings::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $module = $navigationItemCollection->get(EventAdmin::NAV_ITEM);
            $settings = new NavigationItem(static::NAV_ITEM);
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

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::FORM_VIEW, '/config')
                    ->setResourceKey(EventSettings::RESOURCE_KEY)
                    ->setFormKey(EventSettings::FORM_KEY)
                    ->setTabTitle('sulu_event.config.tab')
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