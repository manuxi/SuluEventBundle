<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Admin;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\AdminBundle\Exception\NavigationItemNotFoundException;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class LocationAdmin extends Admin
{
    public const NAV_ITEM = 'sulu_event.locations';

    public const LIST_VIEW = 'sulu_event.location.list';
    public const ADD_FORM_VIEW = 'sulu_event.location.add_form';
    public const ADD_FORM_DETAILS_VIEW = 'sulu_event.location.add_form.details';
    public const EDIT_FORM_VIEW = 'sulu_event.location.edit_form';
    public const EDIT_FORM_DETAILS_VIEW = 'sulu_event.location.edit_form.details';

    private $viewBuilderFactory;
    private $securityChecker;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        SecurityCheckerInterface $securityChecker
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker = $securityChecker;
    }

    /**
     * @throws NavigationItemNotFoundException
     */
    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $module = $navigationItemCollection->get(EventAdmin::NAV_ITEM);
            $locations = new NavigationItem(static::NAV_ITEM);
            $locations->setPosition(10);
            $locations->setView(static::LIST_VIEW);

            $module->addChild($locations);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $formToolbarActions = [];
        $listToolbarActions = [];

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

        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {

            $listView = $this->viewBuilderFactory->createListViewBuilder(self::LIST_VIEW, '/locations')
                ->setResourceKey(Location::RESOURCE_KEY)
                ->setListKey(Location::LIST_KEY)
                ->setTitle('sulu_event.locations')
                ->addListAdapters(['table'])
                ->setAddView(static::ADD_FORM_VIEW)
                ->setEditView(static::EDIT_FORM_VIEW)
                ->addToolbarActions($listToolbarActions);
            $viewCollection->add($listView);

            $addFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(self::ADD_FORM_VIEW, '/locations/add')
                ->setResourceKey(Location::RESOURCE_KEY)
                ->setBackView(static::LIST_VIEW);
            $viewCollection->add($addFormView);

            $addDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(self::ADD_FORM_DETAILS_VIEW, '/details')
                ->setResourceKey(Location::RESOURCE_KEY)
                ->setFormKey('location_details')
                ->setTabTitle('sulu_admin.details')
                ->setEditView(static::EDIT_FORM_VIEW)
                ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                ->setParent(static::ADD_FORM_VIEW);
            $viewCollection->add($addDetailsFormView);

            $editFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, '/locations/:id')
                ->setResourceKey(Location::RESOURCE_KEY)
                ->setBackView(static::LIST_VIEW)
                ->setTitleProperty('title');
            $viewCollection->add($editFormView);

            $editDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::EDIT_FORM_DETAILS_VIEW, '/details')
                ->setResourceKey(Location::RESOURCE_KEY)
                ->setFormKey(Location::FORM_KEY)
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::EDIT_FORM_VIEW);
            $viewCollection->add($editDetailsFormView);
        }
    }
}
