<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Repository;

use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;

/**
 * "Highlights only" (filter "featured" with the name of the tag) for the calendar API and the iCal feed.
 */
class EventRepositoryFeaturedFilterTest extends AbstractEventDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createEvent('plain', 'de', '2027-03-01 10:00:00', tags: ['symfony']);
        $this->createEvent('star', 'de', '2027-03-02 10:00:00', tags: ['highlight']);
        $this->createEvent('star-more', 'de', '2027-03-03 10:00:00', categories: ['ai'], tags: ['symfony', 'highlight', 'llm']);
        $this->createEvent('star-en', 'en', '2027-03-04 10:00:00', tags: ['highlight']);
    }

    public function testOnlyEventsWithTheFeaturedTagAreReturned(): void
    {
        $filters = ['locale' => 'de', 'featured' => true, 'featuredTag' => 'Highlight'];

        $this->assertSame(['star', 'star-more'], $this->uuids($this->repository->findForIcal($filters)));
        $this->assertSame(['star', 'star-more'], $this->uuids($this->repository->findForCalendar($filters)));
    }

    public function testWithoutTheFlagNothingChanges(): void
    {
        $filters = ['locale' => 'de', 'featured' => false, 'featuredTag' => 'Highlight'];

        $this->assertSame(['plain', 'star', 'star-more'], $this->uuids($this->repository->findForIcal($filters)));
    }

    public function testWithoutAFeaturedTagNameNoEventIsAHighlight(): void
    {
        $this->assertSame([], $this->repository->findForIcal(['locale' => 'de', 'featured' => true, 'featuredTag' => null]));
        $this->assertSame([], $this->repository->findForCalendar(['locale' => 'de', 'featured' => true]));
    }

    public function testHighlightsCombineWithTheOtherFilters(): void
    {
        $events = $this->repository->findForIcal([
            'locale'      => 'de',
            'featured'    => true,
            'featuredTag' => 'highlight',
            'categories'  => [$this->categories['ai']->getId()],
            'tags'        => ['symfony'],
        ]);

        $this->assertSame(['star-more'], $this->uuids($events), 'one row, although the event has three tags');
    }
}
