<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Domain\Event\Event\CreatedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\PublishedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\RemovedEvent;
use Manuxi\SuluEventBundle\Service\EventFilterOptions;
use Manuxi\SuluEventBundle\Service\FilterOptionsCacheListener;
use PHPUnit\Framework\TestCase;

class FilterOptionsCacheListenerTest extends TestCase
{
    public function testItListensToEveryChangeOfAnEvent(): void
    {
        $subscribed = FilterOptionsCacheListener::getSubscribedEvents();

        $this->assertArrayHasKey(CreatedEvent::class, $subscribed);
        $this->assertArrayHasKey(PublishedEvent::class, $subscribed);
        $this->assertArrayHasKey(RemovedEvent::class, $subscribed);
        $this->assertCount(9, $subscribed);
        $this->assertSame(['clear'], array_values(array_unique($subscribed)));
    }

    public function testClearForgetsTheFilterOptions(): void
    {
        $filterOptions = $this->createMock(EventFilterOptions::class);
        $filterOptions->expects($this->once())->method('clear');

        (new FilterOptionsCacheListener($filterOptions))->clear();
    }
}
