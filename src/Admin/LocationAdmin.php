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
    public const LOCATION_LIST_KEY = 'locations';

    public const LOCATION_LIST_VIEW = 'app.locations_list';

    public const LOCATION_ADD_FORM_VIEW = 'app.location_add_form';

    public const LOCATION_EDIT_FORM_VIEW = 'app.location_edit_form';

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
            $module = $navigationItemCollection->get('app.events');
            $locations = new NavigationItem('app.locations');
            $locations->setPosition(10);
            $locations->setView(static::LOCATION_LIST_VIEW);

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

            $listView = $this->viewBuilderFactory->createListViewBuilder(self::LOCATION_LIST_VIEW, '/locations')
                ->setResourceKey(Location::RESOURCE_KEY)
                ->setListKey(self::LOCATION_LIST_KEY)
                ->setTitle('app.locations')
                ->addListAdapters(['table'])
                ->setAddView(static::LOCATION_ADD_FORM_VIEW)
                ->setEditView(static::LOCATION_EDIT_FORM_VIEW)
                ->addToolbarActions($listToolbarActions);
            $viewCollection->add($listView);

            $addFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(self::LOCATION_ADD_FORM_VIEW, '/locations/add')
                ->setResourceKey('locations')
                ->setBackView(static::LOCATION_LIST_VIEW);
            $viewCollection->add($addFormView);

            $addDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(self::LOCATION_ADD_FORM_VIEW . '.details', '/details')
                ->setResourceKey('locations')
                ->setFormKey('location_details')
                ->setTabTitle('sulu_admin.details')
                ->setEditView(static::LOCATION_EDIT_FORM_VIEW)
                ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                ->setParent(static::LOCATION_ADD_FORM_VIEW);
            $viewCollection->add($addDetailsFormView);

            $editFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(static::LOCATION_EDIT_FORM_VIEW, '/locations/:id')
                ->setResourceKey('locations')
                ->setBackView(static::LOCATION_LIST_VIEW)
                ->setTitleProperty('title');
            $viewCollection->add($editFormView);

            $editDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::LOCATION_EDIT_FORM_VIEW . '.details', '/details')
                ->setResourceKey('locations')
                ->setFormKey('location_details')
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::LOCATION_EDIT_FORM_VIEW);
            $viewCollection->add($editDetailsFormView);
        }
    }
}
