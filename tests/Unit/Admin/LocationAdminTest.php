<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Admin;

use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Admin\LocationAdmin;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ListViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ResourceTabViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class LocationAdminTest extends TestCase
{
    private LocationAdmin $admin;
    private ViewBuilderFactoryInterface $viewBuilderFactory;
    private SecurityCheckerInterface $securityChecker;

    protected function setUp(): void
    {
        $this->viewBuilderFactory = $this->createMock(ViewBuilderFactoryInterface::class);
        $this->securityChecker = $this->createMock(SecurityCheckerInterface::class);

        $this->admin = new LocationAdmin($this->viewBuilderFactory, $this->securityChecker);
    }

    public function testConfigureNavigationItems(): void
    {
        $collection = new NavigationItemCollection();
        $eventItem = new NavigationItem(EventAdmin::NAV_ITEM);
        $collection->add($eventItem);

        $this->securityChecker->method('hasPermission')->with(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)->willReturn(true);

        $this->admin->configureNavigationItems($collection);

        $this->assertCount(1, $eventItem->getChildren());
        $this->assertEquals(LocationAdmin::NAV_ITEM, $eventItem->getChildren()[0]->getName());
    }

    public function testConfigureViews(): void
    {
        $viewCollection = new ViewCollection();

        $this->securityChecker->method('hasPermission')->willReturn(true);

        $listViewBuilder = $this->createMock(ListViewBuilderInterface::class);
        $listViewBuilder->method('setResourceKey')->willReturnSelf();
        $listViewBuilder->method('setListKey')->willReturnSelf();
        $listViewBuilder->method('setTitle')->willReturnSelf();
        $listViewBuilder->method('addListAdapters')->willReturnSelf();
        $listViewBuilder->method('setAddView')->willReturnSelf();
        $listViewBuilder->method('setEditView')->willReturnSelf();
        $listViewBuilder->method('addToolbarActions')->willReturnSelf();

        $this->viewBuilderFactory->method('createListViewBuilder')->willReturn($listViewBuilder);

        // Mock ResourceTabViewBuilders
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilderInterface::class);
        $resourceTabViewBuilder->method('setResourceKey')->willReturnSelf();
        $resourceTabViewBuilder->method('setBackView')->willReturnSelf();
        $resourceTabViewBuilder->method('setTitleProperty')->willReturnSelf();
        $this->viewBuilderFactory->method('createResourceTabViewBuilder')->willReturn($resourceTabViewBuilder);

        // Mock FormViewBuilders
        $formViewBuilder = $this->createMock(FormViewBuilderInterface::class);
        $formViewBuilder->method('setResourceKey')->willReturnSelf();
        $formViewBuilder->method('setFormKey')->willReturnSelf();
        $formViewBuilder->method('setTabTitle')->willReturnSelf();
        $formViewBuilder->method('setEditView')->willReturnSelf();
        $formViewBuilder->method('addToolbarActions')->willReturnSelf();
        $formViewBuilder->method('setParent')->willReturnSelf();
        $this->viewBuilderFactory->method('createFormViewBuilder')->willReturn($formViewBuilder);

        $this->admin->configureViews($viewCollection);

        $this->assertNotEmpty($viewCollection->all());
    }
}
