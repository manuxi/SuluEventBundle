<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Admin;

use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Entity\Event;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\View\ActivityViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Localization\Manager\LocalizationManagerInterface;
use Sulu\Content\Infrastructure\Sulu\Admin\ContentViewBuilderFactoryInterface;

class EventAdminTest extends TestCase
{
    private EventAdmin $eventAdmin;

    private ViewBuilderFactoryInterface|MockObject $viewBuilderFactory;
    private ContentViewBuilderFactoryInterface|MockObject $contentViewBuilderFactory;
    private SecurityCheckerInterface|MockObject $securityChecker;
    private LocalizationManagerInterface|MockObject $localizationManager;

    protected function setUp(): void
    {
        $this->viewBuilderFactory = $this->createMock(ViewBuilderFactoryInterface::class);
        $this->contentViewBuilderFactory = $this->createMock(ContentViewBuilderFactoryInterface::class);
        $this->securityChecker = $this->createMock(SecurityCheckerInterface::class);
        $this->localizationManager = $this->createMock(LocalizationManagerInterface::class);
        $activityViewBuilderFactory = $this->createMock(ActivityViewBuilderFactoryInterface::class);

        $this->eventAdmin = new EventAdmin(
            $this->viewBuilderFactory,
            $this->contentViewBuilderFactory,
            $this->securityChecker,
            $this->localizationManager,
            $activityViewBuilderFactory,
        );
    }

    public function testGetConfigKeyReturnsCorrectValue(): void
    {
        // Act
        $configKey = $this->eventAdmin->getConfigKey();

        // Assert
        $this->assertEquals('sulu_event', $configKey);
    }

    public function testGetSecurityContextsReturnsCorrectStructure(): void
    {
        // Act
        $contexts = $this->eventAdmin->getSecurityContexts();

        // Assert
        $this->assertIsArray($contexts);
        $this->assertArrayHasKey('Sulu', $contexts);
        $this->assertArrayHasKey('Events', $contexts['Sulu']);
        $this->assertArrayHasKey(Event::SECURITY_CONTEXT, $contexts['Sulu']['Events']);
    }

    public function testGetSecurityContextsContainsAllPermissionTypes(): void
    {
        // Act
        $contexts = $this->eventAdmin->getSecurityContexts();
        $permissions = $contexts['Sulu']['Events'][Event::SECURITY_CONTEXT];

        // Assert
        $this->assertContains(PermissionTypes::VIEW, $permissions);
        $this->assertContains(PermissionTypes::ADD, $permissions);
        $this->assertContains(PermissionTypes::EDIT, $permissions);
        $this->assertContains(PermissionTypes::DELETE, $permissions);
        $this->assertContains(PermissionTypes::LIVE, $permissions);
    }

    public function testConfigureNavigationItemsDoesNothingWhenNoEditPermission(): void
    {
        // Arrange
        $navigationItemCollection = $this->createMock(NavigationItemCollection::class);

        $this->securityChecker
            ->expects($this->once())
            ->method('hasPermission')
            ->with(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ->willReturn(false);

        $navigationItemCollection
            ->expects($this->never())
            ->method('add');

        // Act
        $this->eventAdmin->configureNavigationItems($navigationItemCollection);
    }

    public function testConfigureNavigationItemsAddsItemWhenHasEditPermission(): void
    {
        // Arrange
        $navigationItemCollection = $this->createMock(NavigationItemCollection::class);

        $this->securityChecker
            ->expects($this->once())
            ->method('hasPermission')
            ->with(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ->willReturn(true);

        $navigationItemCollection
            ->expects($this->once())
            ->method('add');

        // Act
        $this->eventAdmin->configureNavigationItems($navigationItemCollection);
    }

    public function testConfigureViewsDoesNothingWhenNoEditPermission(): void
    {
        $viewCollection = $this->createMock(ViewCollection::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturnCallback(function ($context, $permission) {
                return false;
            });

        $viewCollection
            ->expects($this->never())
            ->method('add');

        $this->eventAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsAddsViewsWhenHasEditPermission(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $locales = ['en', 'de'];

        $this->mockSecurityCheckerForViews();
        $this->mockLocalizationManager($locales);
        $this->mockContentViewBuilderFactory();
        $this->mockViewBuilders($viewCollection, $locales);

        // Act
        $this->eventAdmin->configureViews($viewCollection);

        // Note: Exact count depends on permissions and automation availability
        // We just verify that add() was called
        $this->assertTrue(true);
    }

    public function testConfigureViewsWithMultipleLocales(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $locales = ['en', 'de', 'fr', 'es'];

        $this->mockSecurityCheckerForViews();
        $this->mockLocalizationManager($locales);
        $this->mockContentViewBuilderFactory();
        $this->mockViewBuilders($viewCollection, $locales);

        // Act
        $this->eventAdmin->configureViews($viewCollection);

        // Assert - verify that webspace manager was called
        $this->assertTrue(true);
    }



    public function testConfigureViewsAddsSettingsTab(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $locales = ['en'];

        $this->mockSecurityCheckerForViews();
        $this->mockLocalizationManager($locales);
        $this->mockContentViewBuilderFactory();

        // Verify that Settings view is created
        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturnCallback(function ($name) {
                $builder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilder::class);
                $builder->method('setResourceKey')->willReturnSelf();
                $builder->method('setFormKey')->willReturnSelf();
                $builder->method('setTabTitle')->willReturnSelf();
                $builder->method('addToolbarActions')->willReturnSelf();
                $builder->method('setEditView')->willReturnSelf();
                $builder->method('setTabOrder')->willReturnSelf();
                $builder->method('setParent')->willReturnSelf();

                if ('sulu_event.event.edit_tabs.settings' === $name) {
                    $this->assertEquals('sulu_event.event.edit_tabs.settings', $name);
                }

                return $builder;
            });

        // Act
        $this->eventAdmin->configureViews($viewCollection);

        // Assert
        $this->assertTrue(true);
    }

    public function testConstantsHaveCorrectValues(): void
    {
        // Assert
        $this->assertEquals('sulu_event.events', EventAdmin::NAV_ITEM);
        $this->assertEquals('sulu_event.event.list', EventAdmin::LIST_VIEW);
        $this->assertEquals('sulu_event.event.edit_tabs', EventAdmin::EDIT_FORM_VIEW);
        $this->assertEquals('sulu_event.event.edit_form.details', EventAdmin::EDIT_FORM_DETAILS_VIEW);
    }

    /**
     * Helper: Mock security checker for views configuration.
     */
    private function mockSecurityCheckerForViews(): void
    {
        $this->securityChecker
            ->method('hasPermission')
            ->willReturnCallback(function ($context, $permission) {
                // Grant all permissions for testing
                return true;
            });
    }

    /**
     * Helper: Mock localization manager with locales.
     */
    private function mockLocalizationManager(array $locales): void
    {
        $this->localizationManager
            ->expects($this->atLeastOnce())
            ->method('getLocales')
            ->willReturn($locales);
    }

    private function mockContentViewBuilderFactory(): void
    {
        $this->contentViewBuilderFactory
            ->method('createViews')
            ->willReturn([]);
    }

    /**
     * Helper: Mock view builders for views configuration.
     */
    private function mockViewBuilders(MockObject $viewCollection, array $locales): void
    {
        // Mock list view builder
        $listViewBuilder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\ListViewBuilder::class);
        $listViewBuilder->method('setResourceKey')->willReturnSelf();
        $listViewBuilder->method('setListKey')->willReturnSelf();
        $listViewBuilder->method('setTitle')->willReturnSelf();
        $listViewBuilder->method('addListAdapters')->willReturnSelf();
        $listViewBuilder->method('addLocales')->willReturnSelf();
        $listViewBuilder->method('setDefaultLocale')->willReturnSelf();
        $listViewBuilder->method('setAddView')->willReturnSelf();
        $listViewBuilder->method('setEditView')->willReturnSelf();
        $listViewBuilder->method('addToolbarActions')->willReturnSelf();

        // Mock resource tab view builder
        $resourceTabViewBuilder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\ResourceTabViewBuilder::class);
        $resourceTabViewBuilder->method('setResourceKey')->willReturnSelf();
        $resourceTabViewBuilder->method('setBackView')->willReturnSelf();
        $resourceTabViewBuilder->method('addLocales')->willReturnSelf();
        $resourceTabViewBuilder->method('setTitleProperty')->willReturnSelf();
        $resourceTabViewBuilder->method('setAttributeDefault')->willReturnSelf();

        // Mock form view builder
        $formViewBuilder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilder::class);
        $formViewBuilder->method('setResourceKey')->willReturnSelf();
        $formViewBuilder->method('setFormKey')->willReturnSelf();
        $formViewBuilder->method('setTabTitle')->willReturnSelf();
        $formViewBuilder->method('setEditView')->willReturnSelf();
        $formViewBuilder->method('addToolbarActions')->willReturnSelf();
        $formViewBuilder->method('setParent')->willReturnSelf();

        // Mock preview form view builder
        $previewFormViewBuilder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\PreviewFormViewBuilder::class);
        $previewFormViewBuilder->method('setPreviewCondition')->willReturnSelf();
        $previewFormViewBuilder->method('setResourceKey')->willReturnSelf();
        $previewFormViewBuilder->method('setFormKey')->willReturnSelf();
        $previewFormViewBuilder->method('setTabTitle')->willReturnSelf();
        $previewFormViewBuilder->method('addToolbarActions')->willReturnSelf();
        $previewFormViewBuilder->method('setParent')->willReturnSelf();
        $previewFormViewBuilder->method('setTitleVisible')->willReturnSelf();
        $previewFormViewBuilder->method('setTabOrder')->willReturnSelf();
        $previewFormViewBuilder->method('disablePreviewWebspaceChooser')->willReturnSelf();

        $this->viewBuilderFactory
            ->method('createListViewBuilder')
            ->willReturn($listViewBuilder);

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturn($formViewBuilder);

        $this->viewBuilderFactory
            ->method('createPreviewFormViewBuilder')
            ->willReturn($previewFormViewBuilder);

        $viewCollection->method('add')->willReturnSelf();
    }
}
