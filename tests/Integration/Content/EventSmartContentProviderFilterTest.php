<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Content;

use Doctrine\ORM\QueryBuilder;
use Manuxi\SuluEventBundle\Content\EventSmartContentProvider;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The detail filters of the smart content provider (types list of the admin, query parameters of the website) against
 * real queries.
 */
class EventSmartContentProviderFilterTest extends AbstractEventDatabaseTestCase
{
    private EventSmartContentProvider $provider;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        parent::setUp();

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn (string $id): string => '[' . $id . ']');

        $this->requestStack = new RequestStack();

        // the constructor asks the entity manager for repositories that need the container; the filters do not
        $reflection     = new \ReflectionClass(EventSmartContentProvider::class);
        $this->provider = $reflection->newInstanceWithoutConstructor();
        foreach ([
            'entityManager' => $this->entityManager,
            'translator'    => $translator,
            'requestStack'  => $this->requestStack,
            'eventTypes'    => ['conference' => ['name' => 'c', 'color' => '#fff'], 'meetup' => ['name' => 'm', 'color' => '#000']],
        ] as $property => $value) {
            $reflection->getProperty($property)->setValue($this->provider, $value);
        }

        $tomorrow  = (new \DateTimeImmutable('+1 day'))->format('Y-m-d 10:00:00');
        $yesterday = (new \DateTimeImmutable('-3 days'))->format('Y-m-d 10:00:00');
        $this->createEvent('online-free', 'de', $tomorrow, type: 'conference', attendanceMode: 'online', isFree: true);
        $this->createEvent('onsite-berlin', 'de', $tomorrow, type: 'meetup', attendanceMode: 'offline', location: 'Messe Berlin');
        $this->createEvent('mixed-vegas', 'de', $tomorrow, type: 'conference', attendanceMode: 'mixed', location: 'Convention Center', isFree: false);
        $this->createEvent('past-online', 'de', $yesterday, type: 'conference', attendanceMode: 'online');
    }

    public function testAdminTypesListOffersTheDetailFilters(): void
    {
        $types = array_column($this->callProtected('getTypes'), 'title', 'type');

        $this->assertArrayHasKey('pending', $types);
        $this->assertArrayHasKey('conference', $types);
        $this->assertSame('[sulu_event.field.attendance_mode]: [sulu_event.attendance_mode.online]', $types['mode:online']);
        $this->assertArrayHasKey('mode:offline', $types);
        $this->assertArrayHasKey('mode:mixed', $types);
        $this->assertSame('[sulu_event.field.is_free]', $types['free']);
        // countries of the locations in use only, named in the language of the user
        $this->assertArrayHasKey('country:DE', $types);
        $this->assertArrayHasKey('country:US', $types);
        $this->assertArrayNotHasKey('country:FR', $types);
        $this->assertStringStartsWith('[sulu_event.filter.country]: ', $types['country:DE']);
    }

    public function testAdminFilterByAttendanceMode(): void
    {
        $this->assertSame(['online-free', 'past-online'], $this->found(['mode:online']));
        $this->assertSame(['mixed-vegas', 'online-free', 'past-online'], $this->found(['mode:online', 'mode:mixed']));
    }

    public function testAdminFilterByCountryAndFree(): void
    {
        $this->assertSame(['onsite-berlin'], $this->found(['country:DE']));
        $this->assertSame(['mixed-vegas', 'onsite-berlin'], $this->found(['country:DE', 'country:US']));
        $this->assertSame(['online-free'], $this->found(['free']));
    }

    public function testAdminFiltersCombineWithTheEventTypesAndPending(): void
    {
        $this->assertSame(['mixed-vegas'], $this->found(['conference', 'country:US']));
        $this->assertSame(['online-free'], $this->found(['conference', 'mode:online', 'pending']));
        $this->assertSame(['past-online'], $this->found(['mode:online', 'expired']));
    }

    public function testPendingCombinesWithTheOtherFilters(): void
    {
        // "pending" is an OR of two conditions (Doctrine puts them in parentheses); the other filters still apply
        $this->assertSame(['mixed-vegas'], $this->found(['mode:mixed', 'pending']));
        $this->assertSame([], $this->found(['mode:offline', 'free', 'pending']));
    }

    public function testWebsiteQueryParameters(): void
    {
        $this->assertSame(['online-free', 'past-online'], $this->foundForRequest(['attendanceMode' => 'online']));
        $this->assertSame(['mixed-vegas', 'online-free', 'past-online'], $this->foundForRequest(['attendanceMode' => ['online', 'mixed']]));
        $this->assertSame(['onsite-berlin'], $this->foundForRequest(['country' => 'de']));
        $this->assertSame(['online-free'], $this->foundForRequest(['free' => '1']));
        $this->assertSame(['mixed-vegas'], $this->foundForRequest(['attendanceMode' => 'online,mixed', 'country' => 'US']));
    }

    public function testWebsiteParametersWithoutUsableValueMatchNothing(): void
    {
        $this->assertSame([], $this->foundForRequest(['attendanceMode' => 'hybrid']));
        $this->assertSame([], $this->foundForRequest(['country' => 'Germany']));
    }

    public function testWebsiteParametersAreOptionalAndRenamable(): void
    {
        $all = ['mixed-vegas', 'onsite-berlin', 'online-free', 'past-online'];
        sort($all);

        $this->assertSame($all, $this->foundForRequest([]));
        $this->assertSame($all, $this->foundForRequest(['free' => '0']));
        $this->assertSame(['online-free'], $this->foundForRequest(['kostenlos' => 'yes'], ['free_parameter' => 'kostenlos']));
        $this->assertSame(['onsite-berlin'], $this->foundForRequest(['land' => 'DE'], ['country_parameter' => 'land']));
    }

    /**
     * @param string[] $types
     *
     * @return string[]
     */
    private function found(array $types): array
    {
        $queryBuilder = $this->createQueryBuilder();
        $this->callProtected('addTypeFilters', $queryBuilder, $types, 'dimensionContent');

        return $this->uuidsOf($queryBuilder);
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $params
     *
     * @return string[]
     */
    private function foundForRequest(array $query, array $params = []): array
    {
        $this->requestStack->push(Request::create('/events', 'GET', $query));
        $queryBuilder = $this->createQueryBuilder();
        $method       = new \ReflectionMethod($this->provider, 'addWebsiteDetailFilters');
        $method->invoke($this->provider, $queryBuilder, 'dimensionContent', $params);
        $this->requestStack->pop();

        return $this->uuidsOf($queryBuilder);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event')
            ->innerJoin('event.dimensionContents', 'dimensionContent', 'WITH', "dimensionContent.locale = 'de' AND dimensionContent.stage = 'live'");
    }

    /**
     * @return string[]
     */
    private function uuidsOf(QueryBuilder $queryBuilder): array
    {
        $uuids = array_map(static fn (Event $event): string => $event->getUuid(), $queryBuilder->getQuery()->getResult());
        sort($uuids);

        return $uuids;
    }

    private function callProtected(string $method, mixed ...$arguments): mixed
    {
        return (new \ReflectionMethod($this->provider, $method))->invoke($this->provider, ...$arguments);
    }
}
