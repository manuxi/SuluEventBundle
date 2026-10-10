<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Controller\Website;

use Manuxi\SuluEventBundle\Controller\Website\ICalController;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\ICalGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class ICalControllerTest extends TestCase
{
    private const UUID = '019bf796-423c-7e1f-969c-5c4ece5e9b73';

    private ICalGenerator&MockObject $generator;
    private EventRepository&MockObject $repository;
    private ContentAggregatorInterface&MockObject $aggregator;
    private ICalController $controller;

    protected function setUp(): void
    {
        $this->generator  = $this->createMock(ICalGenerator::class);
        $this->repository = $this->createMock(EventRepository::class);
        $this->aggregator = $this->createMock(ContentAggregatorInterface::class);

        $this->controller = new ICalController($this->generator, $this->repository, $this->aggregator);
    }

    public function testSingleActionLoadsTheEventByUuidAndReturnsTheCalendarFile(): void
    {
        $event   = new Event(self::UUID);
        $content = $this->createMock(EventDimensionContent::class);
        $content->method('getTitle')->willReturn('Test Event');

        $this->repository->expects($this->once())->method('findByUuid')->with(self::UUID)->willReturn($event);
        $this->aggregator->expects($this->once())->method('aggregate')
            ->with($event, ['locale' => 'de', 'stage' => 'live'])
            ->willReturn($content);
        $this->generator->expects($this->once())->method('generateSingle')->with($event, $content)->willReturn("BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n");

        $response = $this->controller->singleAction(self::UUID, 'de');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/calendar; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename="event-' . self::UUID . '.ics"', $response->headers->get('Content-Disposition'));
        $this->assertSame("BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n", $response->getContent());
    }

    public function testSingleActionReturns404ForUnknownEvent(): void
    {
        $this->repository->method('findByUuid')->willReturn(null);
        $this->generator->expects($this->never())->method('generateSingle');

        $this->expectException(NotFoundHttpException::class);

        $this->controller->singleAction(self::UUID, 'de');
    }

    public function testSingleActionReturns404WhenTheEventIsNotPublishedInTheLocale(): void
    {
        $event = new Event(self::UUID);
        $this->repository->method('findByUuid')->willReturn($event);
        $this->aggregator->method('aggregate')->willThrowException(new ContentNotFoundException($event, ['locale' => 'fr']));
        $this->generator->expects($this->never())->method('generateSingle');

        $this->expectException(NotFoundHttpException::class);

        $this->controller->singleAction(self::UUID, 'fr');
    }

    public function testSingleActionReturns404WhenTheLocaleHasNoTitle(): void
    {
        $event   = new Event(self::UUID);
        $content = $this->createMock(EventDimensionContent::class);
        $content->method('getTitle')->willReturn(null);
        $this->repository->method('findByUuid')->willReturn($event);
        $this->aggregator->method('aggregate')->willReturn($content);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->singleAction(self::UUID, 'en');
    }

    public function testSingleRouteMatchesUuidsOnly(): void
    {
        $routes  = (new YamlFileLoader(new FileLocator(__DIR__ . '/../../../../src/Resources/config')))->load('routes_website.yaml');
        $matcher = new UrlMatcher($routes, new RequestContext());

        $match = $matcher->match('/events/' . self::UUID . '/download.de.ics');
        $this->assertSame('sulu_event.ical_single', $match['_route']);
        $this->assertSame(self::UUID, $match['id']);
        $this->assertSame('de', $match['_locale']);

        $this->expectException(ResourceNotFoundException::class);
        $matcher->match('/events/42/download.de.ics');
    }

    public function testSingleRouteRequirementOfTheControllerIsTheSameUuidPattern(): void
    {
        $routes = (new YamlFileLoader(new FileLocator(__DIR__ . '/../../../../src/Resources/config')))->load('routes_website.yaml');

        $this->assertSame(ICalController::UUID_REQUIREMENT, $routes->get('sulu_event.ical_single')->getRequirement('id'));
    }
}
