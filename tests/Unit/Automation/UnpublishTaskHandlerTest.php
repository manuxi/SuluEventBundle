<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Automation\UnpublishTaskHandler;
use Manuxi\SuluEventBundle\Domain\Event\Event\UnpublishedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UnpublishTaskHandlerTest extends TestCase
{
    private UnpublishTaskHandler $taskHandler;

    private EntityManagerInterface|MockObject $entityManager;
    private TranslatorInterface|MockObject $translator;
    private DomainEventCollectorInterface|MockObject $domainEventCollector;
    private EventDispatcherInterface|MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->domainEventCollector = $this->createMock(DomainEventCollectorInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->taskHandler = new UnpublishTaskHandler(
            $this->entityManager,
            $this->translator,
            $this->domainEventCollector,
            $this->dispatcher
        );
    }

    public function testSupportsEventClass(): void
    {
        // Act
        $supports = $this->taskHandler->supports(Event::class);

        // Assert
        $this->assertTrue($supports);
    }

    public function testSupportsEventSubclass(): void
    {
        // Arrange - Create a mock subclass
        $subclass = new class extends Event {};

        // Act
        $supports = $this->taskHandler->supports(get_class($subclass));

        // Assert
        $this->assertTrue($supports);
    }

    public function testDoesNotSupportOtherClasses(): void
    {
        // Act
        $supports = $this->taskHandler->supports(\stdClass::class);

        // Assert
        $this->assertFalse($supports);
    }

    public function testGetConfigurationReturnsCorrectTranslation(): void
    {
        // Arrange
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('sulu_event.unpublish', [], 'admin')
            ->willReturn('Unpublish Event');

        // Act
        $config = $this->taskHandler->getConfiguration();

        // Assert
        $this->assertInstanceOf(TaskHandlerConfiguration::class, $config);
    }

    public function testConfigureOptionsResolverRequiresIdAndLocale(): void
    {
        // Arrange
        $optionsResolver = new OptionsResolver();

        // Act
        $configuredResolver = $this->taskHandler->configureOptionsResolver($optionsResolver);

        // Assert - Trying to resolve without required options should throw exception
        $this->expectException(MissingOptionsException::class);
        $configuredResolver->resolve([]);
    }

    public function testConfigureOptionsResolverAcceptsValidOptions(): void
    {
        // Arrange
        $optionsResolver = new OptionsResolver();
        $configuredResolver = $this->taskHandler->configureOptionsResolver($optionsResolver);

        // Act
        $resolved = $configuredResolver->resolve([
            'id' => '456',
            'locale' => 'de',
        ]);

        // Assert
        $this->assertEquals('456', $resolved['id']);
        $this->assertEquals('de', $resolved['locale']);
    }

    public function testConfigureOptionsResolverRequiresStringId(): void
    {
        // Arrange
        $optionsResolver = new OptionsResolver();
        $configuredResolver = $this->taskHandler->configureOptionsResolver($optionsResolver);

        // Assert
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);

        // Act - Pass integer instead of string
        $configuredResolver->resolve([
            'id' => 456,
            'locale' => 'de',
        ]);
    }

    public function testConfigureOptionsResolverRequiresStringLocale(): void
    {
        // Arrange
        $optionsResolver = new OptionsResolver();
        $configuredResolver = $this->taskHandler->configureOptionsResolver($optionsResolver);

        // Assert
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);

        // Act - Pass boolean instead of string
        $configuredResolver->resolve([
            'id' => '456',
            'locale' => true,
        ]);
    }

    public function testHandleDoesNothingWhenWorkloadIsNotArray(): void
    {
        // Arrange
        $this->entityManager
            ->expects($this->never())
            ->method('getRepository');

        // Act
        $this->taskHandler->handle('invalid');
        $this->taskHandler->handle(999);
        $this->taskHandler->handle(false);
    }

    public function testHandleDoesNothingWhenEntityNotFound(): void
    {
        // Arrange
        $workload = [
            'class' => Event::class,
            'id' => '888',
            'locale' => 'fr',
        ];

        $repository = $this->createMock(EventRepository::class);
        $repository
            ->expects($this->once())
            ->method('findById')
            ->with(888, 'fr')
            ->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Event::class)
            ->willReturn($repository);

        $this->domainEventCollector
            ->expects($this->never())
            ->method('collect');

        // Act
        $this->taskHandler->handle($workload);
    }

    public function testHandleUnpublishesEventEntity(): void
    {
        // Arrange
        $workload = [
            'class' => Event::class,
            'id' => '7',
            'locale' => 'it',
        ];

        $event = $this->createMock(Event::class);

        $event->expects($this->once())
            ->method('setPublished')
            ->with(false);

        $repository = $this->createMock(EventRepository::class);
        $repository
            ->expects($this->once())
            ->method('findById')
            ->with(7, 'it')
            ->willReturn($event);

        $repository
            ->expects($this->once())
            ->method('save')
            ->with($event);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Event::class)
            ->willReturn($repository);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch'); // PreUpdated + Updated

        // Act
        $this->taskHandler->handle($workload);
    }

    public function testHandleDispatchesSearchPreUpdatedEvent(): void
    {
        // Arrange
        $workload = [
            'class' => Event::class,
            'id' => '2',
            'locale' => 'en',
        ];

        $event = $this->createMock(Event::class);
        $event->method('setPublished')->willReturnSelf();

        $repository = $this->createMock(EventRepository::class);
        $repository->method('findById')->willReturn($event);
        $repository->method('save')->willReturn($event);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        // Verify PreUpdated is dispatched first
        $dispatchOrder = [];
        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($event) use (&$dispatchOrder) {
                $dispatchOrder[] = get_class($event);

                return $event;
            });

        // Act
        $this->taskHandler->handle($workload);

        // Assert - PreUpdated should be first
        $this->assertCount(2, $dispatchOrder);
        $this->assertStringContainsString('PreUpdatedEvent', $dispatchOrder[0]);
    }

    public function testHandleDispatchesSearchUpdatedEvent(): void
    {
        // Arrange
        $workload = [
            'class' => Event::class,
            'id' => '3',
            'locale' => 'es',
        ];

        $event = $this->createMock(Event::class);
        $event->method('setPublished')->willReturnSelf();

        $repository = $this->createMock(EventRepository::class);
        $repository->method('findById')->willReturn($event);
        $repository->method('save')->willReturn($event);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        // Verify Updated is dispatched after save
        $dispatchOrder = [];
        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($event) use (&$dispatchOrder) {
                $dispatchOrder[] = get_class($event);

                return $event;
            });

        // Act
        $this->taskHandler->handle($workload);

        // Assert - Updated should be second
        $this->assertCount(2, $dispatchOrder);
        $this->assertStringContainsString('UpdatedEvent', $dispatchOrder[1]);
    }

    public function testHandleCollectsDomainEvent(): void
    {
        // Arrange
        $workload = [
            'class' => Event::class,
            'id' => '15',
            'locale' => 'pt',
        ];

        $entity = $this->createMock(Event::class);
        $entity->method('setPublished')->willReturnSelf();

        $repository = $this->createMock(EventRepository::class);
        $repository->method('findById')->willReturn($entity);
        $repository->method('save')->willReturn($entity);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->dispatcher->method('dispatch')->willReturnArgument(0);

        // Verify domain event is collected
        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect')
            ->willReturnCallback(function ($event) use ($entity, $workload) {
                $this->assertInstanceOf(UnpublishedEvent::class, $event);

                return null;
            });

        // Act
        $this->taskHandler->handle($workload);
    }

    public function testHandleWithDifferentLocales(): void
    {
        // Test with different locales - each locale gets its own test instance
        $locales = ['en', 'de', 'fr', 'es', 'it'];

        foreach ($locales as $locale) {
            // Create fresh mocks for each iteration
            $entityManager = $this->createMock(EntityManagerInterface::class);
            $dispatcher = $this->createMock(EventDispatcherInterface::class);
            $domainEventCollector = $this->createMock(DomainEventCollectorInterface::class);

            $taskHandler = new UnpublishTaskHandler(
                $entityManager,
                $this->translator,
                $domainEventCollector,
                $dispatcher
            );

            $workload = [
                'class' => Event::class,
                'id' => '5',
                'locale' => $locale,
            ];

            $event = $this->createMock(Event::class);
            $event->method('setPublished')->willReturnSelf();

            $repository = $this->createMock(EventRepository::class);
            $repository
                ->expects($this->once())
                ->method('findById')
                ->with(5, $locale)
                ->willReturn($event);

            $repository->method('save')->willReturn($event);

            $entityManager
                ->method('getRepository')
                ->willReturn($repository);

            $dispatcher->method('dispatch')->willReturnArgument(0);

            // Act
            $taskHandler->handle($workload);
        }

        // If we reach here, all locales were handled successfully
        $this->assertTrue(true);
    }

    public function testHandleConvertsStringIdToInteger(): void
    {
        // Arrange
        $workload = [
            'class' => Event::class,
            'id' => '789',
            'locale' => 'ja',
        ];

        $event = $this->createMock(Event::class);
        $event->method('setPublished')->willReturnSelf();

        $repository = $this->createMock(EventRepository::class);

        // Verify integer conversion
        $repository
            ->expects($this->once())
            ->method('findById')
            ->with(789, 'ja') // Should be integer, not string
            ->willReturn($event);

        $repository->method('save')->willReturn($event);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->dispatcher->method('dispatch')->willReturnArgument(0);

        // Act
        $this->taskHandler->handle($workload);
    }

    public function testHandleSavesEntityBeforeCollectingDomainEvent(): void
    {
        // Arrange
        $workload = [
            'class' => Event::class,
            'id' => '20',
            'locale' => 'ko',
        ];

        $event = $this->createMock(Event::class);
        $event->method('setPublished')->willReturnSelf();

        $callOrder = [];

        $repository = $this->createMock(EventRepository::class);
        $repository->method('findById')->willReturn($event);
        $repository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($entity) use (&$callOrder) {
                $callOrder[] = 'save';

                return $entity;
            });

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect')
            ->willReturnCallback(function ($event) use (&$callOrder) {
                $callOrder[] = 'collect';

                return null;
            });

        $this->dispatcher->method('dispatch')->willReturnArgument(0);

        // Act
        $this->taskHandler->handle($workload);

        // Assert - save should be called before collect
        $this->assertEquals('save', $callOrder[0]);
        $this->assertEquals('collect', $callOrder[1]);
    }

    public function testHandleCollectsDomainEventBeforeDispatchingUpdated(): void
    {
        // Arrange
        $workload = [
            'class' => Event::class,
            'id' => '99',
            'locale' => 'ru',
        ];

        $event = $this->createMock(Event::class);
        $event->method('setPublished')->willReturnSelf();

        $callOrder = [];
        $collectedEvent = null; // Store the collected event

        $repository = $this->createMock(EventRepository::class);
        $repository->method('findById')->willReturn($event);
        $repository->method('save')->willReturn($event);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect')
            ->willReturnCallback(function ($domainEvent) use (&$callOrder, &$collectedEvent) {
                $callOrder[] = 'collect_domain';
                $collectedEvent = $domainEvent; // Capture it for later assertion
                // void method - no return
            });

        $this->dispatcher
            ->method('dispatch')
            ->willReturnCallback(function ($event) use (&$callOrder) {
                $className = get_class($event);
                if (false !== strpos($className, 'PreUpdatedEvent')) {
                    $callOrder[] = 'dispatch_pre_updated';
                } elseif (false !== strpos($className, 'UpdatedEvent')) {
                    $callOrder[] = 'dispatch_updated';
                }

                return $event;
            });

        // Act
        $this->taskHandler->handle($workload);

        // Assert - Check the collected domain event type
        $this->assertInstanceOf(UnpublishedEvent::class, $collectedEvent);

        // Assert - Expected order: PreUpdated -> collect -> Updated
        $this->assertGreaterThanOrEqual(3, count($callOrder), 'All three calls should happen');

        $preUpdatedIndex = array_search('dispatch_pre_updated', $callOrder);
        $collectIndex = array_search('collect_domain', $callOrder);
        $updatedIndex = array_search('dispatch_updated', $callOrder);

        $this->assertNotFalse($preUpdatedIndex, 'dispatch_pre_updated was called');
        $this->assertNotFalse($collectIndex, 'collect_domain was called');
        $this->assertNotFalse($updatedIndex, 'dispatch_updated was called');

        // PreUpdated should be first
        $this->assertLessThan($collectIndex, $preUpdatedIndex, 'PreUpdated should be before collect');
        // Collect should be before Updated
        $this->assertLessThan($updatedIndex, $collectIndex, 'collect should be before Updated');
    }

    public function testHandleSetsPublishedToFalseNotTrue(): void
    {
        // Arrange
        $workload = [
            'class' => Event::class,
            'id' => '50',
            'locale' => 'zh',
        ];

        $event = $this->createMock(Event::class);

        // Ensure setPublished is called with false, not true
        $event->expects($this->once())
            ->method('setPublished')
            ->with($this->identicalTo(false));

        $repository = $this->createMock(EventRepository::class);
        $repository->method('findById')->willReturn($event);
        $repository->method('save')->willReturn($event);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->dispatcher->method('dispatch')->willReturnArgument(0);
        $this->dispatcher->method('dispatch')->willReturnArgument(0);

        // Act
        $this->taskHandler->handle($workload);
    }
}
