<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Service\EventTimezoneResolver;
use PHPUnit\Framework\TestCase;

class EventTimezoneResolverTest extends TestCase
{
    private EventDimensionContent $content;

    protected function setUp(): void
    {
        $this->content = new EventDimensionContent(new Event('019bf796-423c-7e1f-969c-5c4ece5e9b73'));
    }

    public function testOrderIsEventLocationApplicationPhpDefault(): void
    {
        $resolver = new EventTimezoneResolver('Europe/Berlin');
        $this->assertSame('Europe/Berlin', $resolver->resolve($this->content)->getName());

        $location = new Location();
        $location->setTimezone('America/Los_Angeles');
        $this->content->setLocation($location);
        $this->assertSame('America/Los_Angeles', $resolver->resolve($this->content)->getName());

        $this->content->setTimezone('Asia/Taipei');
        $this->assertSame('Asia/Taipei', $resolver->resolve($this->content)->getName());

        $this->assertSame(date_default_timezone_get(), (new EventTimezoneResolver(null))->resolve(new EventDimensionContent(new Event()))->getName());
    }

    public function testAnUnknownStoredNameDoesNotBreakTheOutput(): void
    {
        // set directly, as an old or imported value could be there; the setters would refuse it
        $reflection = new \ReflectionProperty(EventDimensionContent::class, 'timezone');
        $reflection->setValue($this->content, 'Not/AZone');

        $this->assertSame('Europe/Berlin', (new EventTimezoneResolver('Europe/Berlin'))->resolve($this->content)->getName());
    }

    public function testToInstantReadsTheTimeOfDayInTheZoneOfTheEvent(): void
    {
        $resolver = new EventTimezoneResolver('Europe/Berlin');
        $this->content->setTimezone('America/Los_Angeles');

        // whatever zone the hydrated object carries, "10:00" means 10:00 in Los Angeles
        $instant = $resolver->toInstant(new \DateTimeImmutable('2027-07-01 10:00:00', new \DateTimeZone('UTC')), $this->content);

        $this->assertSame('2027-07-01T10:00:00-07:00', $instant->format('c'));
        $this->assertSame('2027-07-01T17:00:00+00:00', $instant->setTimezone(new \DateTimeZone('UTC'))->format('c'));
    }
}
