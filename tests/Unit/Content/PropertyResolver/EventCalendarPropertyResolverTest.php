<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\PropertyResolver;

use Manuxi\SuluEventBundle\Content\PropertyResolver\EventCalendarPropertyResolver;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\ContentResolver\Value\ContentView;

class EventCalendarPropertyResolverTest extends TestCase
{
    private EventCalendarPropertyResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new EventCalendarPropertyResolver();
    }

    public function testGetType(): void
    {
        $this->assertEquals('event_calendar', EventCalendarPropertyResolver::getType());
    }

    public function testResolveWithEmptyData(): void
    {
        $result = $this->resolver->resolve([], 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $content = $result->getContent();

        $this->assertIsArray($content);
        $this->assertArrayHasKey('filters', $content);
        $this->assertArrayHasKey('categories', $content['filters']);
        $this->assertArrayHasKey('tags', $content['filters']);
    }

    public function testResolveWithNullData(): void
    {
        $result = $this->resolver->resolve(null, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $content = $result->getContent();

        $this->assertIsArray($content);
        $this->assertArrayHasKey('filters', $content);
    }

    public function testResolveWithCompleteData(): void
    {
        $data = [
            'title' => 'Upcoming Events',
            'subtitle' => 'Join us',
            'text' => 'Calendar description',
            'initialView' => 'timeGridWeek',
            'showFilters' => true,
            'categories' => [1, 2, 3],
            'tags' => [10, 20],
        ];

        $result = $this->resolver->resolve($data, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $content = $result->getContent();

        $this->assertEquals('Upcoming Events', $content['title']);
        $this->assertEquals('Join us', $content['subtitle']);
        $this->assertEquals('Calendar description', $content['text']);
        $this->assertEquals('timeGridWeek', $content['initialView']);
        $this->assertTrue($content['showFilters']);
        $this->assertEquals([1, 2, 3], $content['filters']['categories']);
        $this->assertEquals([10, 20], $content['filters']['tags']);
    }

    public function testResolveWithDefaultValues(): void
    {
        $result = $this->resolver->resolve([], 'en');

        $content = $result->getContent();

        $this->assertNull($content['title']);
        $this->assertNull($content['subtitle']);
        $this->assertNull($content['text']);
        $this->assertEquals('dayGridMonth', $content['initialView']);
        $this->assertFalse($content['showFilters']);
        $this->assertEquals([], $content['filters']['categories']);
        $this->assertEquals([], $content['filters']['tags']);
    }

    public function testResolveWithParams(): void
    {
        $data = ['title' => 'Events'];
        $params = ['customParam' => 'value'];

        $result = $this->resolver->resolve($data, 'en', $params);

        $content = $result->getContent();
        $this->assertEquals('Events', $content['title']);
        $this->assertEquals('value', $content['customParam']);
    }
}