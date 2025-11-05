<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity\Models;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Models\EventModel;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventModelTest extends TestCase
{
    private EventModel $eventModel;

    private EventRepository|MockObject $eventRepository;

    private LocationRepository|MockObject $locationRepository;
    private MediaRepositoryInterface|MockObject $mediaRepository;
    private ContactRepository|MockObject $contactRepository;
    private RouteManagerInterface|MockObject $routeManager;
    private RouteRepositoryInterface|MockObject $routeRepository;
    private EntityManagerInterface|MockObject $entityManager;
    private DomainEventCollectorInterface|MockObject $domainEventCollector;
    private EventDispatcherInterface|MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->locationRepository = $this->createMock(LocationRepository::class);
        $this->mediaRepository = $this->createMock(MediaRepositoryInterface::class);
        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->routeManager = $this->createMock(RouteManagerInterface::class);
        $this->routeRepository = $this->createMock(RouteRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->domainEventCollector = $this->createMock(DomainEventCollectorInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->eventModel = new EventModel(
            $this->eventRepository,
            $this->locationRepository,
            $this->mediaRepository,
            $this->contactRepository,
            $this->routeManager,
            $this->routeRepository,
            $this->entityManager,
            $this->domainEventCollector,
            $this->dispatcher
        );
    }

    public function testGetEventWithoutRequestReturnsEvent(): void
    {
        // Arrange
        $eventId = 1;
        $event = $this->createMock(Event::class);

        $this->eventRepository
            ->expects($this->once())
            ->method('find')
            ->with($eventId)
            ->willReturn($event);

        // Act
        $result = $this->eventModel->getEvent($eventId);

        // Assert
        $this->assertSame($event, $result);
    }

    public function testGetEventWithRequestReturnsEventInLocale(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request(['locale' => 'en']);
        $event = $this->createMock(Event::class);

        $this->eventRepository
            ->expects($this->once())
            ->method('findById')
            ->with($eventId, 'en')
            ->willReturn($event);

        // Act
        $result = $this->eventModel->getEvent($eventId, $request);

        // Assert
        $this->assertSame($event, $result);
    }

    public function testGetEventThrowsEntityNotFoundExceptionWhenNotFound(): void
    {
        // Arrange
        $eventId = 999;

        $this->eventRepository
            ->expects($this->once())
            ->method('find')
            ->with($eventId)
            ->willReturn(null);

        $this->eventRepository
            ->expects($this->once())
            ->method('getClassName')
            ->willReturn(Event::class);

        // Assert
        $this->expectException(EntityNotFoundException::class);

        // Act
        $this->eventModel->getEvent($eventId);
    }

    public function testDeleteEventRemovesEntityAndRoutes(): void
    {
        // Arrange
        $eventId = 1;
        $event = $this->createMock(Event::class);
        $event->expects($this->any())
            ->method('getId')
            ->willReturn($eventId);
        $event->expects($this->any())
            ->method('getTitle')
            ->willReturn('Test Event');
        $event->expects($this->any())
            ->method('getLocale')
            ->willReturn('en');

        $route = $this->createMock(Route::class);

        $this->routeRepository
            ->expects($this->once())
            ->method('findAllByEntity')
            ->with(Event::class, (string) $eventId, 'en')
            ->willReturn([$route]);

        $this->routeRepository
            ->expects($this->once())
            ->method('remove')
            ->with($route);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch');

        $this->eventRepository
            ->expects($this->once())
            ->method('remove')
            ->with($eventId);

        // Act
        $this->eventModel->deleteEvent($event);
    }

    public function testCreateEventCreatesAndSavesEntity(): void
    {
        // Arrange
        $request = new Request(
            ['locale' => 'en'],
            ['title' => 'Test Event', 'routePath' => '/test-event']
        );

        $event = $this->createMock(Event::class);
        $event->expects($this->any())->method('getId')->willReturn(1);
        $event->expects($this->any())->method('getLocale')->willReturn('en');
        $event->expects($this->any())->method('getRoutePath')->willReturn('/test-event');

        $this->eventRepository
            ->expects($this->once())
            ->method('create')
            ->with('en')
            ->willReturn($event);

        $event->expects($this->once())
            ->method('setTitle')
            ->with('Test Event');

        $event->expects($this->once())
            ->method('setRoutePath')
            ->with('/test-event');

        $this->eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($event)
            ->willReturn($event);

        $this->routeManager
            ->expects($this->once())
            ->method('createOrUpdateByAttributes')
            ->with(Event::class, '1', 'en', '/test-event');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch');

        // Act
        $result = $this->eventModel->createEvent($request);

        // Assert
        $this->assertSame($event, $result);
    }

    public function testUpdateEventUpdatesEntity(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request(
            ['locale' => 'en'],
            ['title' => 'Updated Event', 'routePath' => '/updated-event']
        );

        $event = $this->createMock(Event::class);
        $event->expects($this->any())->method('getId')->willReturn($eventId);
        $event->expects($this->any())->method('getLocale')->willReturn('en');
        $event->expects($this->any())->method('getRoutePath')->willReturn('/updated-event');

        $this->eventRepository
            ->expects($this->once())
            ->method('findById')
            ->with($eventId, 'en')
            ->willReturn($event);

        $event->expects($this->once())
            ->method('setTitle')
            ->with('Updated Event');

        $this->eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($event)
            ->willReturn($event);

        $this->routeManager
            ->expects($this->once())
            ->method('createOrUpdateByAttributes');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch'); // PreUpdated + Updated

        // Act
        $result = $this->eventModel->updateEvent($eventId, $request);

        // Assert
        $this->assertSame($event, $result);
    }

    public function testPublishEventPublishesEntity(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request(['locale' => 'en']);

        $event = $this->createMock(Event::class);

        $this->eventRepository
            ->expects($this->once())
            ->method('findById')
            ->with($eventId, 'en')
            ->willReturn($event);

        $this->eventRepository
            ->expects($this->once())
            ->method('publish')
            ->with($event)
            ->willReturn($event);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch'); // PreUpdated + Updated

        // Act
        $result = $this->eventModel->publish($eventId, $request);

        // Assert
        $this->assertSame($event, $result);
    }

    public function testUnpublishEventUnpublishesEntity(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request(['locale' => 'en']);

        $event = $this->createMock(Event::class);

        $this->eventRepository
            ->expects($this->once())
            ->method('findById')
            ->with($eventId, 'en')
            ->willReturn($event);

        $this->eventRepository
            ->expects($this->once())
            ->method('unpublish')
            ->with($event)
            ->willReturn($event);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch'); // PreUpdated + Updated

        // Act
        $result = $this->eventModel->unpublish($eventId, $request);

        // Assert
        $this->assertSame($event, $result);
    }

    public function testCopyLanguageCreatesNewTranslations(): void
    {
        // Arrange
        $eventId = 1;
        $srcLocale = 'en';
        $destLocales = ['de', 'fr'];
        $request = new Request(['locale' => 'de']); // <- Request muss locale enthalten

        $event = $this->createMock(Event::class);

        $this->eventRepository
            ->expects($this->once())
            ->method('find')
            ->with($eventId)
            ->willReturn($event);

        $event->expects($this->exactly(2)) // <- Called twice: once with srcLocale, once with request locale
        ->method('setLocale')
            ->withConsecutive([$srcLocale], ['de']); // <- srcLocale first, then request locale

        // copyToLocale is called for each destination locale
        $event->expects($this->exactly(2))
            ->method('copyToLocale')
            ->withConsecutive(['de'], ['fr'])
            ->willReturnSelf();

        $this->eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($event)
            ->willReturn($event);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch');

        // Act
        $result = $this->eventModel->copyLanguage($eventId, $request, $srcLocale, $destLocales);

        // Assert
        $this->assertSame($event, $result);
    }

    public function testCopyLanguageCopiesEventToMultipleLocales(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request(['locale' => 'en']);
        $srcLocale = 'en';
        $destLocales = ['de', 'fr'];

        $event = $this->createMock(Event::class);

        $this->eventRepository
            ->expects($this->once())
            ->method('find')
            ->with($eventId)
            ->willReturn($event);

        $event->expects($this->exactly(2))
            ->method('setLocale')
            ->withConsecutive([$srcLocale], ['en']);

        $event->expects($this->exactly(2))
            ->method('copyToLocale')
            ->willReturnSelf();

        $this->eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($event)
            ->willReturn($event);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch');

        // Act
        $result = $this->eventModel->copyLanguage($eventId, $request, $srcLocale, $destLocales);

        // Assert
        $this->assertSame($event, $result);
    }
}
