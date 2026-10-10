<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Repository;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class EventRepositoryFeedFilterTest extends AbstractEventDatabaseTestCase
{
    public function testIcalWithoutFiltersReturnsEveryLiveEventOfTheLocale(): void
    {
        $this->createEvent('a', 'de', '2027-03-01 10:00:00');
        $this->createEvent('b', 'de', '2027-02-01 10:00:00');
        $this->createEvent('c', 'en', '2027-01-01 10:00:00');
        $this->createEvent('draft', 'de', '2027-01-15 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT);

        $events = $this->repository->findForIcal(['locale' => 'de']);

        // ordered by start date; the draft-only event and the English one are not part of the German feed
        $this->assertSame(['b', 'a'], $this->uuids($events));
    }

    public function testIcalFiltersByCategoryIdWithAnyOperator(): void
    {
        $this->createEvent('ai', 'de', '2027-03-01 10:00:00', categories: ['ai']);
        $this->createEvent('games', 'de', '2027-03-02 10:00:00', categories: ['games']);
        $this->createEvent('both', 'de', '2027-03-03 10:00:00', categories: ['ai', 'games']);
        $this->createEvent('none', 'de', '2027-03-04 10:00:00');

        $events = $this->repository->findForIcal(['locale' => 'de', 'categories' => [$this->categories['ai']->getId()]]);
        $this->assertSame(['ai', 'both'], $this->uuids($events));

        // a query string delivers the ids as strings; an event with two matching categories must not show up twice
        $events = $this->repository->findForIcal([
            'locale'     => 'de',
            'categories' => [(string) $this->categories['ai']->getId(), (string) $this->categories['games']->getId()],
        ]);
        $this->assertSame(['ai', 'games', 'both'], $this->uuids($events));
    }

    public function testIcalFiltersByCategoryWithAllOperator(): void
    {
        $this->createEvent('ai', 'de', '2027-03-01 10:00:00', categories: ['ai']);
        $this->createEvent('both', 'de', '2027-03-03 10:00:00', categories: ['ai', 'games']);

        $events = $this->repository->findForIcal([
            'locale'           => 'de',
            'categories'       => [$this->categories['ai']->getId(), $this->categories['games']->getId()],
            'categoryOperator' => 'AND',
        ]);

        $this->assertSame(['both'], $this->uuids($events));
    }

    public function testIcalFiltersByTagName(): void
    {
        $this->createEvent('symfony', 'de', '2027-03-01 10:00:00', tags: ['symfony']);
        $this->createEvent('llm', 'de', '2027-03-02 10:00:00', tags: ['llm']);
        $this->createEvent('none', 'de', '2027-03-03 10:00:00');

        $events = $this->repository->findForIcal(['locale' => 'de', 'tags' => ['symfony']]);

        $this->assertSame(['symfony'], $this->uuids($events));
    }

    public function testCombinedCategoryAndTagFilterNarrowsDown(): void
    {
        $this->createEvent('ai-llm', 'de', '2027-03-01 10:00:00', categories: ['ai'], tags: ['llm']);
        $this->createEvent('ai-only', 'de', '2027-03-02 10:00:00', categories: ['ai']);
        $this->createEvent('llm-only', 'de', '2027-03-03 10:00:00', tags: ['llm']);

        $events = $this->repository->findForIcal([
            'locale'     => 'de',
            'categories' => [$this->categories['ai']->getId()],
            'tags'       => ['llm'],
        ]);

        $this->assertSame(['ai-llm'], $this->uuids($events));
    }

    public function testFilterWithoutUsableValueMatchesNothing(): void
    {
        $this->createEvent('ai', 'de', '2027-03-01 10:00:00', categories: ['ai']);

        $this->assertSame([], $this->repository->findForIcal(['locale' => 'de', 'categories' => ['abc']]));
        $this->assertSame([], $this->repository->findForIcal(['locale' => 'de', 'tags' => ['']]));
    }

    public function testEmptyFilterListsAreIgnored(): void
    {
        $this->createEvent('ai', 'de', '2027-03-01 10:00:00', categories: ['ai']);

        $events = $this->repository->findForIcal(['locale' => 'de', 'categories' => [], 'tags' => [], 'location' => null]);

        $this->assertSame(['ai'], $this->uuids($events));
    }

    public function testCalendarFiltersByCategoryTagAndLocation(): void
    {
        $this->createEvent('a', 'de', '2027-03-01 10:00:00', categories: ['ai'], tags: ['llm'], location: 'Messe Berlin');
        $this->createEvent('b', 'de', '2027-03-02 10:00:00', categories: ['ai'], location: 'Online');
        $this->createEvent('c', 'de', '2027-03-03 10:00:00', categories: ['games'], location: 'Messe Berlin');

        $byCategory = $this->repository->findForCalendar(['locale' => 'de', 'categories' => [$this->categories['ai']->getId()]]);
        $this->assertSame(['a', 'b'], $this->uuids($byCategory));

        $byTag = $this->repository->findForCalendar(['locale' => 'de', 'tags' => ['llm']]);
        $this->assertSame(['a'], $this->uuids($byTag));

        $byLocationId = $this->repository->findForCalendar(['locale' => 'de', 'location' => $this->locations['Online']->getId()]);
        $this->assertSame(['b'], $this->uuids($byLocationId));

        $byLocationName = $this->repository->findForCalendar(['locale' => 'de', 'location' => 'Messe Berlin']);
        $this->assertSame(['a', 'c'], $this->uuids($byLocationName));

        $combined = $this->repository->findForCalendar([
            'locale'     => 'de',
            'categories' => [$this->categories['ai']->getId()],
            'location'   => 'Messe Berlin',
        ]);
        $this->assertSame(['a'], $this->uuids($combined));
    }

    public function testCalendarReturnsEventsOverlappingTheRange(): void
    {
        // a three day fair that begins before the visible range still has to be shown
        $this->createEvent('fair', 'de', '2027-03-01 09:00:00', '2027-03-03 18:00:00');
        $this->createEvent('before', 'de', '2027-02-10 09:00:00', '2027-02-11 18:00:00');
        $this->createEvent('after', 'de', '2027-03-20 09:00:00', '2027-03-20 18:00:00');
        $this->createEvent('inside', 'de', '2027-03-02 20:00:00');
        $this->createEvent('before-no-end', 'de', '2027-02-28 20:00:00');
        $this->createEvent('draft', 'de', '2027-03-02 09:00:00', stage: DimensionContentInterface::STAGE_DRAFT);

        $events = $this->repository->findForCalendar([
            'locale' => 'de',
            'start'  => '2027-03-02 00:00:00',
            'end'    => '2027-03-09 00:00:00',
        ]);

        $this->assertSame(['fair', 'inside'], $this->uuids($events));
    }

    public function testCalendarWithoutRangeReturnsEveryLiveEvent(): void
    {
        $this->createEvent('a', 'de', '2027-03-01 10:00:00');
        $this->createEvent('b', 'de', '2020-03-01 10:00:00');

        $this->assertSame(['b', 'a'], $this->uuids($this->repository->findForCalendar(['locale' => 'de'])));
    }

    public function testFiltersByType(): void
    {
        $this->createEvent('conf', 'de', '2027-03-01 10:00:00', type: 'conference');
        $this->createEvent('fair', 'de', '2027-03-02 10:00:00', type: 'trade_fair');
        $this->createEvent('meetup', 'de', '2027-03-03 10:00:00', type: 'meetup');

        $this->assertSame(['conf', 'meetup'], $this->uuids($this->repository->findForIcal(['locale' => 'de', 'types' => ['conference', 'meetup']])));
        $this->assertSame(['fair'], $this->uuids($this->repository->findForCalendar(['locale' => 'de', 'types' => ['trade_fair']])));
        $this->assertSame([], $this->repository->findForIcal(['locale' => 'de', 'types' => ['']]));
    }

    public function testFiltersByAttendanceMode(): void
    {
        $this->createEvent('onsite', 'de', '2027-03-01 10:00:00', attendanceMode: 'offline');
        $this->createEvent('stream', 'de', '2027-03-02 10:00:00', attendanceMode: 'online');
        $this->createEvent('both', 'de', '2027-03-03 10:00:00', attendanceMode: 'mixed');

        $this->assertSame(['stream'], $this->uuids($this->repository->findForIcal(['locale' => 'de', 'attendanceModes' => ['online']])));
        $this->assertSame(['stream', 'both'], $this->uuids($this->repository->findForCalendar(['locale' => 'de', 'attendanceModes' => ['online', 'mixed']])));
        // an unknown mode matches nothing, valid ones next to it still count
        $this->assertSame([], $this->repository->findForIcal(['locale' => 'de', 'attendanceModes' => ['hybrid']]));
        $this->assertSame(['stream'], $this->uuids($this->repository->findForIcal(['locale' => 'de', 'attendanceModes' => ['hybrid', 'online']])));
    }

    public function testFiltersByCountryOfTheLocation(): void
    {
        $this->createEvent('berlin', 'de', '2027-03-01 10:00:00', location: 'Messe Berlin');
        $this->createEvent('vegas', 'de', '2027-03-02 10:00:00', location: 'Convention Center');
        $this->createEvent('stream', 'de', '2027-03-03 10:00:00', location: 'Online');
        $this->createEvent('nowhere', 'de', '2027-03-04 10:00:00');

        // codes are compared case-insensitively
        $this->assertSame(['berlin'], $this->uuids($this->repository->findForIcal(['locale' => 'de', 'countries' => ['de']])));
        $this->assertSame(['berlin', 'vegas'], $this->uuids($this->repository->findForCalendar(['locale' => 'de', 'countries' => ['DE', 'US']])));
        $this->assertSame([], $this->repository->findForIcal(['locale' => 'de', 'countries' => ['Germany']]));
    }

    public function testFreeFilterOnlyKeepsFreeEvents(): void
    {
        $this->createEvent('free', 'de', '2027-03-01 10:00:00', isFree: true);
        $this->createEvent('paid', 'de', '2027-03-02 10:00:00', isFree: false);
        $this->createEvent('unknown', 'de', '2027-03-03 10:00:00');

        $this->assertSame(['free'], $this->uuids($this->repository->findForIcal(['locale' => 'de', 'free' => true])));
        $this->assertSame(['free', 'paid', 'unknown'], $this->uuids($this->repository->findForIcal(['locale' => 'de', 'free' => false])));
    }

    public function testDetailFiltersCombineWithCategories(): void
    {
        $this->createEvent('a', 'de', '2027-03-01 10:00:00', categories: ['ai'], type: 'conference', attendanceMode: 'online', isFree: true);
        $this->createEvent('b', 'de', '2027-03-02 10:00:00', categories: ['ai'], type: 'conference', attendanceMode: 'offline', isFree: true);
        $this->createEvent('c', 'de', '2027-03-03 10:00:00', categories: ['games'], type: 'conference', attendanceMode: 'online', isFree: true);

        $events = $this->repository->findForIcal([
            'locale' => 'de',
            'categories' => [$this->categories['ai']->getId()],
            'types' => ['conference'],
            'attendanceModes' => ['online'],
            'free' => true,
        ]);

        $this->assertSame(['a'], $this->uuids($events));
    }

    public function testFeedEventsCarryAllTheirDimensionContents(): void
    {
        // the content aggregator needs the complete, already loaded collection (it throws in debug mode otherwise)
        $event = new Event('with-draft');
        foreach ([DimensionContentInterface::STAGE_LIVE, DimensionContentInterface::STAGE_DRAFT] as $stage) {
            $content = new EventDimensionContent($event);
            $content->setStage($stage);
            $content->setLocale('de');
            $content->setTitle('Event');
            $content->setStartDate(new \DateTimeImmutable('2027-03-01 10:00:00'));
            $event->addDimensionContent($content);
        }
        $this->entityManager->persist($event);
        $this->entityManager->flush();
        $this->entityManager->clear();

        foreach ([$this->repository->findForIcal(['locale' => 'de']), $this->repository->findForCalendar(['locale' => 'de'])] as $events) {
            $this->assertCount(1, $events);
            $dimensionContents = $events[0]->getDimensionContents();
            $this->assertTrue($dimensionContents->isInitialized());
            $this->assertCount(2, $dimensionContents);
            $this->entityManager->clear();
        }
    }

}
