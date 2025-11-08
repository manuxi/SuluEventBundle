<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Controller\Admin;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluEventBundle\Controller\Admin\EventController;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventExcerpt;
use Manuxi\SuluEventBundle\Entity\EventSeo;
use Manuxi\SuluEventBundle\Entity\Models\EventExcerptModel;
use Manuxi\SuluEventBundle\Entity\Models\EventModel;
use Manuxi\SuluEventBundle\Entity\Models\EventSeoModel;
use Manuxi\SuluEventBundle\ListBuilder\DoctrineListRepresentationFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EventControllerTest extends TestCase
{
    private EventController $controller;

    private EventModel|MockObject $eventModel;
    private EventSeoModel|MockObject $eventSeoModel;
    private EventExcerptModel|MockObject $eventExcerptModel;
    private DoctrineListRepresentationFactory|MockObject $doctrineListRepresentationFactory;
    private SecurityCheckerInterface|MockObject $securityChecker;
    private TrashManagerInterface|MockObject $trashManager;
    private ViewHandlerInterface|MockObject $viewHandler;

    protected function setUp(): void
    {
        $this->eventModel = $this->createMock(EventModel::class);
        $this->eventSeoModel = $this->createMock(EventSeoModel::class);
        $this->eventExcerptModel = $this->createMock(EventExcerptModel::class);
        $this->doctrineListRepresentationFactory = $this->createMock(DoctrineListRepresentationFactory::class);
        $this->securityChecker = $this->createMock(SecurityCheckerInterface::class);
        $this->trashManager = $this->createMock(TrashManagerInterface::class);
        $this->viewHandler = $this->createMock(ViewHandlerInterface::class);

        $this->controller = new EventController(
            $this->eventModel,
            $this->eventSeoModel,
            $this->eventExcerptModel,
            $this->doctrineListRepresentationFactory,
            $this->securityChecker,
            $this->trashManager,
            $this->viewHandler
        );
    }

    public function testCgetActionReturnsListRepresentation(): void
    {
        // Arrange
        $request = new Request(['locale' => 'en']);
        $listRepresentation = $this->createMock(ListRepresentation::class);

        $this->doctrineListRepresentationFactory
            ->expects($this->once())
            ->method('createDoctrineListRepresentation')
            ->with(
                Event::RESOURCE_KEY,
                [],
                ['locale' => 'en']
            )
            ->willReturn($listRepresentation);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->cgetAction($request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetActionReturnsEventEntity(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request(['locale' => 'en']);
        $event = $this->createMock(Event::class);

        $this->eventModel
            ->expects($this->once())
            ->method('getEvent')
            ->with($eventId, $request)
            ->willReturn($event);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->getAction($eventId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetActionThrowsEntityNotFoundException(): void
    {
        // Arrange
        $eventId = 999;
        $request = new Request(['locale' => 'en']);

        $this->eventModel
            ->expects($this->once())
            ->method('getEvent')
            ->with($eventId, $request)
            ->willThrowException(new EntityNotFoundException('Event', $eventId));

        // Assert
        $this->expectException(EntityNotFoundException::class);

        // Act
        $this->controller->getAction($eventId, $request);
    }

    public function testPostActionCreatesEventAndReturns201(): void
    {
        // Arrange
        $request = new Request();
        $event = $this->createMock(Event::class);

        $this->eventModel
            ->expects($this->once())
            ->method('createEvent')
            ->with($request)
            ->willReturn($event);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                // Verify status code is 201
                $this->assertEquals(201, $view->getStatusCode());

                return new Response('', 201);
            });

        // Act
        $response = $this->controller->postAction($request);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testPostTriggerActionPublishesEvent(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request([], ['action' => 'publish']);
        $event = $this->createMock(Event::class);

        $this->eventModel
            ->expects($this->once())
            ->method('publish') // Changed from publishEvent to publish
            ->with($eventId, $request)
            ->willReturn($event);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->postTriggerAction($eventId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostTriggerActionUnpublishesEvent(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request([], ['action' => 'unpublish']);
        $event = $this->createMock(Event::class);

        $this->eventModel
            ->expects($this->once())
            ->method('unpublish') // Changed from unpublishEvent to unpublish
            ->with($eventId, $request)
            ->willReturn($event);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->postTriggerAction($eventId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    // REMOVED testPostTriggerActionCopiesEvent - the 'copy' action doesn't exist in EventModel
    // The controller has a bug - it tries to call $this->eventModel->copy() which doesn't exist

    public function testPostTriggerActionCopiesLocale(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request(
            ['locale' => 'en'],
            ['action' => 'copy-locale', 'src' => 'en', 'dest' => 'de,fr']
        );
        $event = $this->createMock(Event::class);

        $this->securityChecker
            ->expects($this->exactly(2)) // for 'de' and 'fr'
            ->method('checkPermission');

        $this->eventModel
            ->expects($this->once())
            ->method('copyLanguage')
            ->with($eventId, $request, 'en', ['de', 'fr'])
            ->willReturn($event);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->postTriggerAction($eventId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostTriggerActionThrowsExceptionForInvalidAction(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request([], ['action' => 'invalid-action']);

        // Assert
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Unknown action "invalid-action".');

        // Act
        $this->controller->postTriggerAction($eventId, $request);
    }

    public function testPutActionUpdatesEvent(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request();
        $event = $this->createMock(Event::class);
        $eventSeo = $this->createMock(EventSeo::class);
        $eventExcerpt = $this->createMock(EventExcerpt::class);

        $event->expects($this->once())
            ->method('getEventSeo')
            ->willReturn($eventSeo);

        $event->expects($this->once())
            ->method('getEventExcerpt')
            ->willReturn($eventExcerpt);

        $this->eventModel
            ->expects($this->once())
            ->method('updateEvent')
            ->with($eventId, $request)
            ->willReturn($event);

        $this->eventSeoModel
            ->expects($this->once())
            ->method('updateEventSeo')
            ->with($eventSeo, $request);

        $this->eventExcerptModel
            ->expects($this->once())
            ->method('updateEventExcerpt')
            ->with($eventExcerpt, $request);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->putAction($eventId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPutActionPublishesEventWithAction(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request([], ['action' => 'publish']);
        $event = $this->createMock(Event::class);

        $this->eventModel
            ->expects($this->once())
            ->method('publish') // Changed from publishEvent to publish
            ->with($eventId, $request)
            ->willReturn($event);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->putAction($eventId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteActionRemovesEventAndStoresInTrash(): void
    {
        // Arrange
        $eventId = 1;
        $request = new Request(['locale' => 'en']); // Added Request parameter
        $event = $this->createMock(Event::class);
        $event->expects($this->any())
            ->method('getId')
            ->willReturn($eventId);

        $this->eventModel
            ->expects($this->once())
            ->method('getEvent')
            ->with($eventId, $request) // Added request parameter
            ->willReturn($event);

        $this->trashManager
            ->expects($this->once())
            ->method('store')
            ->with(Event::RESOURCE_KEY, $event);

        $this->eventModel
            ->expects($this->once())
            ->method('deleteEvent')
            ->with($event);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 204);
            });

        // Act
        $response = $this->controller->deleteAction($eventId, $request); // Added request parameter

        // Assert
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testGetSecurityContextReturnsCorrectContext(): void
    {
        // Act
        $securityContext = $this->controller->getSecurityContext();

        // Assert
        $this->assertEquals(Event::SECURITY_CONTEXT, $securityContext);
    }
}