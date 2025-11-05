<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Admin;

use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Admin\SettingsAdmin;
use Manuxi\SuluEventBundle\Entity\EventSettings;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilder;
use Sulu\Bundle\AdminBundle\Admin\View\ResourceTabViewBuilder;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class SettingsAdminTest extends TestCase
{
    private SettingsAdmin $settingsAdmin;

    private ViewBuilderFactoryInterface|MockObject $viewBuilderFactory;
    private SecurityCheckerInterface|MockObject $securityChecker;

    protected function setUp(): void
    {
        $this->viewBuilderFactory = $this->createMock(ViewBuilderFactoryInterface::class);
        $this->securityChecker = $this->createMock(SecurityCheckerInterface::class);

        $this->settingsAdmin = new SettingsAdmin(
            $this->viewBuilderFactory,
            $this->securityChecker
        );
    }

    public function testGetConfigKeyReturnsCorrectValue(): void
    {
        // Act
        $configKey = $this->settingsAdmin->getConfigKey();

        // Assert
        $this->assertEquals('sulu_event.config.title', $configKey);
    }

    public function testConstantsHaveCorrectValues(): void
    {
        // Assert
        $this->assertEquals('sulu_event.config', SettingsAdmin::TAB_VIEW);
        $this->assertEquals('sulu_event.config.form', SettingsAdmin::FORM_VIEW);
        $this->assertEquals('sulu_event.config.title.navi', SettingsAdmin::NAV_ITEM_TRANSLATION);
    }

    public function testGetSecurityContextsReturnsCorrectStructure(): void
    {
        // Act
        $contexts = $this->settingsAdmin->getSecurityContexts();

        // Assert
        $this->assertIsArray($contexts);
        $this->assertArrayHasKey('Sulu', $contexts);
        $this->assertArrayHasKey('Events', $contexts['Sulu']); // Changed from 'Event' to 'Events'
        $this->assertArrayHasKey(EventSettings::SECURITY_CONTEXT, $contexts['Sulu']['Events']);
    }

    public function testGetSecurityContextsContainsViewAndEditPermissions(): void
    {
        // Act
        $contexts = $this->settingsAdmin->getSecurityContexts();
        $permissions = $contexts['Sulu']['Events'][EventSettings::SECURITY_CONTEXT]; // Changed from 'Event' to 'Events'

        // Assert
        $this->assertCount(2, $permissions);
        $this->assertContains(PermissionTypes::VIEW, $permissions);
        $this->assertContains(PermissionTypes::EDIT, $permissions);
    }

    public function testGetSecurityContextsDoesNotContainOtherPermissions(): void
    {
        // Act
        $contexts = $this->settingsAdmin->getSecurityContexts();
        $permissions = $contexts['Sulu']['Events'][EventSettings::SECURITY_CONTEXT]; // Changed from 'Event' to 'Events'

        // Assert
        $this->assertNotContains(PermissionTypes::ADD, $permissions);
        $this->assertNotContains(PermissionTypes::DELETE, $permissions);
        $this->assertNotContains(PermissionTypes::LIVE, $permissions);
    }

    public function testConfigureNavigationItemsDoesNothingWhenNoEditPermission(): void
    {
        // Arrange
        $navigationItemCollection = $this->createMock(NavigationItemCollection::class);

        $this->securityChecker
            ->expects($this->once())
            ->method('hasPermission')
            ->with(EventSettings::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ->willReturn(false);

        $navigationItemCollection
            ->expects($this->never())
            ->method('get');

        // Act
        $this->settingsAdmin->configureNavigationItems($navigationItemCollection);
    }

    public function testConfigureNavigationItemsAddsChildWhenHasEditPermission(): void
    {
        // Arrange
        $navigationItemCollection = $this->createMock(NavigationItemCollection::class);
        $parentNavigationItem = $this->createMock(NavigationItem::class);

        $this->securityChecker
            ->expects($this->once())
            ->method('hasPermission')
            ->with(EventSettings::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ->willReturn(true);

        $navigationItemCollection
            ->expects($this->once())
            ->method('get')
            ->with(EventAdmin::NAV_ITEM)
            ->willReturn($parentNavigationItem);

        $parentNavigationItem
            ->expects($this->once())
            ->method('addChild');

        // Act
        $this->settingsAdmin->configureNavigationItems($navigationItemCollection);
    }

    public function testConfigureNavigationItemsCreatesSettingsItemWithCorrectProperties(): void
    {
        // Arrange
        $navigationItemCollection = $this->createMock(NavigationItemCollection::class);
        $parentNavigationItem = $this->createMock(NavigationItem::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $navigationItemCollection
            ->method('get')
            ->with(EventAdmin::NAV_ITEM)
            ->willReturn($parentNavigationItem);

        $capturedChild = null;
        $parentNavigationItem
            ->expects($this->once())
            ->method('addChild')
            ->willReturnCallback(function ($child) use (&$capturedChild) {
                $capturedChild = $child;
            });

        // Act
        $this->settingsAdmin->configureNavigationItems($navigationItemCollection);

        // Assert
        $this->assertInstanceOf(NavigationItem::class, $capturedChild);
    }

    public function testConfigureViewsDoesNothingWhenNoEditPermission(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);

        $this->securityChecker
            ->expects($this->once())
            ->method('hasPermission')
            ->with(EventSettings::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ->willReturn(false);

        $viewCollection
            ->expects($this->never())
            ->method('add');

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsAddsTabViewWhenHasEditPermission(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);
        $this->mockAllFormViewBuilders();

        $this->viewBuilderFactory
            ->expects($this->once())
            ->method('createResourceTabViewBuilder')
            ->with(SettingsAdmin::TAB_VIEW, '/event-settings/:id')
            ->willReturn($resourceTabViewBuilder);

        // Expect: 1 tab view + 3 form views (General, Calendar, Breadcrumbs)
        $viewCollection
            ->expects($this->exactly(4)) // Changed from 2 to 4
            ->method('add');

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsAddsAllFormViewsWhenHasEditPermission(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);

        $formViewNames = [];
        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturnCallback(function ($name, $path) use (&$formViewNames) {
                $formViewNames[] = $name;
                $formViewBuilder = $this->createMock(FormViewBuilder::class);
                $this->mockFormViewBuilder($formViewBuilder);

                return $formViewBuilder;
            });

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);

        // Assert - Check that all three form views were created
        $this->assertCount(3, $formViewNames);
        $this->assertContains(SettingsAdmin::FORM_VIEW_SETTINGS_GENERAL, $formViewNames);
        $this->assertContains(SettingsAdmin::FORM_VIEW_SETTINGS_CALENDAR, $formViewNames);
        $this->assertContains(SettingsAdmin::FORM_VIEW_SETTINGS_BREADCRUMBS, $formViewNames);
    }

    public function testConfigureViewsUsesCorrectResourceKey(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $resourceTabViewBuilder
            ->expects($this->once())
            ->method('setResourceKey')
            ->with(EventSettings::RESOURCE_KEY)
            ->willReturnSelf();

        $resourceTabViewBuilder->method('setAttributeDefault')->willReturnSelf();

        $this->mockAllFormViewBuilders();

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsUsesCorrectFormKeys(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);

        $formKeys = [];
        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturnCallback(function ($name, $path) use (&$formKeys) {
                $formViewBuilder = $this->createMock(FormViewBuilder::class);
                $formViewBuilder->method('setResourceKey')->willReturnSelf();
                $formViewBuilder->method('setTabTitle')->willReturnSelf();
                $formViewBuilder->method('addToolbarActions')->willReturnSelf();
                $formViewBuilder->method('setParent')->willReturnSelf();

                $formViewBuilder
                    ->method('setFormKey')
                    ->willReturnCallback(function ($key) use (&$formKeys, $formViewBuilder) {
                        $formKeys[] = $key;

                        return $formViewBuilder;
                    });

                return $formViewBuilder;
            });

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);

        // Assert - Check all form keys
        $this->assertContains(SettingsAdmin::FORM_KEY_SETTINGS_GENERAL, $formKeys);
        $this->assertContains(SettingsAdmin::FORM_KEY_SETTINGS_CALENDAR, $formKeys);
        $this->assertContains(SettingsAdmin::FORM_KEY_SETTINGS_BREADCRUMBS, $formKeys);
    }

    public function testConfigureViewsSetsAttributeDefaultToDash(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $resourceTabViewBuilder
            ->expects($this->once())
            ->method('setAttributeDefault')
            ->with('id', '-')
            ->willReturnSelf();

        $resourceTabViewBuilder->method('setResourceKey')->willReturnSelf();

        $this->mockAllFormViewBuilders();

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsAddsToolbarActionToAllFormViews(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);

        $toolbarActionCount = 0;
        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturnCallback(function () use (&$toolbarActionCount) {
                $formViewBuilder = $this->createMock(FormViewBuilder::class);
                $formViewBuilder->method('setResourceKey')->willReturnSelf();
                $formViewBuilder->method('setFormKey')->willReturnSelf();
                $formViewBuilder->method('setTabTitle')->willReturnSelf();
                $formViewBuilder->method('setParent')->willReturnSelf();

                $formViewBuilder
                    ->method('addToolbarActions')
                    ->willReturnCallback(function ($actions) use (&$toolbarActionCount, $formViewBuilder) {
                        $this->assertIsArray($actions);
                        $this->assertCount(1, $actions);
                        ++$toolbarActionCount;

                        return $formViewBuilder;
                    });

                return $formViewBuilder;
            });

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);

        // Assert - All 3 form views should have toolbar actions
        $this->assertEquals(3, $toolbarActionCount);
    }

    public function testConfigureViewsSetsCorrectTabTitles(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);

        $tabTitles = [];
        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturnCallback(function () use (&$tabTitles) {
                $formViewBuilder = $this->createMock(FormViewBuilder::class);
                $formViewBuilder->method('setResourceKey')->willReturnSelf();
                $formViewBuilder->method('setFormKey')->willReturnSelf();
                $formViewBuilder->method('addToolbarActions')->willReturnSelf();
                $formViewBuilder->method('setParent')->willReturnSelf();

                $formViewBuilder
                    ->method('setTabTitle')
                    ->willReturnCallback(function ($title) use (&$tabTitles, $formViewBuilder) {
                        $tabTitles[] = $title;

                        return $formViewBuilder;
                    });

                return $formViewBuilder;
            });

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);

        // Assert - Check all tab titles
        $this->assertContains('sulu_event.config.tab.general', $tabTitles);
        $this->assertContains('sulu_event.config.tab.calendar', $tabTitles);
        $this->assertContains('sulu_event.config.tab.breadcrumbs', $tabTitles);
    }

    public function testConfigureViewsSetsCorrectParent(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);

        $parentCount = 0;
        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturnCallback(function () use (&$parentCount) {
                $formViewBuilder = $this->createMock(FormViewBuilder::class);
                $formViewBuilder->method('setResourceKey')->willReturnSelf();
                $formViewBuilder->method('setFormKey')->willReturnSelf();
                $formViewBuilder->method('setTabTitle')->willReturnSelf();
                $formViewBuilder->method('addToolbarActions')->willReturnSelf();

                $formViewBuilder
                    ->method('setParent')
                    ->with(SettingsAdmin::TAB_VIEW)
                    ->willReturnCallback(function ($parent) use (&$parentCount, $formViewBuilder) {
                        ++$parentCount;

                        return $formViewBuilder;
                    });

                return $formViewBuilder;
            });

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);

        // Assert - All 3 form views should have the correct parent set
        $this->assertEquals(3, $parentCount);
    }

    /**
     * Helper: Mock ResourceTabViewBuilder with common behavior.
     */
    private function mockResourceTabViewBuilder(MockObject $builder): void
    {
        $builder->method('setResourceKey')->willReturnSelf();
        $builder->method('setAttributeDefault')->willReturnSelf();
    }

    /**
     * Helper: Mock FormViewBuilder with common behavior.
     */
    private function mockFormViewBuilder(MockObject $builder): void
    {
        $builder->method('setResourceKey')->willReturnSelf();
        $builder->method('setFormKey')->willReturnSelf();
        $builder->method('setTabTitle')->willReturnSelf();
        $builder->method('addToolbarActions')->willReturnSelf();
        $builder->method('setParent')->willReturnSelf();
    }

    /**
     * Helper: Mock all form view builders for tests that need them.
     */
    private function mockAllFormViewBuilders(): void
    {
        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturnCallback(function () {
                $formViewBuilder = $this->createMock(FormViewBuilder::class);
                $this->mockFormViewBuilder($formViewBuilder);

                return $formViewBuilder;
            });
    }
}
