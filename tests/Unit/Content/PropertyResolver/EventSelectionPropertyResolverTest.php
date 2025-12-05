<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\PropertyResolver;

use Manuxi\SuluEventBundle\Content\PropertyResolver\EventSelectionPropertyResolver;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\ContentResolver\Value\ContentView;

class EventSelectionPropertyResolverTest extends TestCase
{
    private EventSelectionPropertyResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new EventSelectionPropertyResolver();
    }

    public function testGetType(): void
    {
        $this->assertEquals('event_selection', EventSelectionPropertyResolver::getType());
    }

    public function testResolveWithEmptyArray(): void
    {
        $result = $this->resolver->resolve([], 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertEquals([], $result->getContent());
        $this->assertEquals(['ids' => []], $result->getView());
    }

    public function testResolveWithNull(): void
    {
        $result = $this->resolver->resolve(null, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertEquals([], $result->getContent());
    }

    public function testResolveWithInvalidType(): void
    {
        $result = $this->resolver->resolve('invalid', 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertEquals([], $result->getContent());
    }

    public function testResolveWithValidIds(): void
    {
        $eventIds = [1, 2, 3];
        $result = $this->resolver->resolve($eventIds, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertEquals(['ids' => [1, 2, 3]], $result->getView());
    }

    public function testResolveWithParams(): void
    {
        $eventIds = [1, 2];
        $params = ['limit' => 5];
        $result = $this->resolver->resolve($eventIds, 'en', $params);

        $this->assertInstanceOf(ContentView::class, $result);
        $view = $result->getView();
        $this->assertEquals([1, 2], $view['ids']);
        $this->assertEquals(5, $view['limit']);
    }
}
