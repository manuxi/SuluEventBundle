<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\PropertyResolver;

use Manuxi\SuluEventBundle\Content\PropertyResolver\SingleEventSelectionPropertyResolver;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\ContentResolver\Value\ContentView;

class SingleEventSelectionPropertyResolverTest extends TestCase
{
    private SingleEventSelectionPropertyResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new SingleEventSelectionPropertyResolver();
    }

    public function testGetType(): void
    {
        $this->assertEquals('single_event_selection', SingleEventSelectionPropertyResolver::getType());
    }

    public function testResolveWithNull(): void
    {
        $result = $this->resolver->resolve(null, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertNull($result->getContent());
        $this->assertEquals(['id' => null], $result->getView());
    }

    public function testResolveWithInvalidType(): void
    {
        // Event uses a UUID string as primary key, so a non-string value (e.g. an int) is invalid.
        $result = $this->resolver->resolve(42, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertNull($result->getContent());
    }

    public function testResolveWithEmptyString(): void
    {
        $result = $this->resolver->resolve('', 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertNull($result->getContent());
    }

    public function testResolveWithValidId(): void
    {
        $eventId = '0191a2b3-c4d5-76e7-8f90-123456789abc';
        $result = $this->resolver->resolve($eventId, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertEquals(['id' => $eventId], $result->getView());
    }

    public function testResolveWithParams(): void
    {
        $eventId = '0191a2b3-c4d5-76e7-8f90-123456789abc';
        $params = ['properties' => ['title', 'startDate']];
        $result = $this->resolver->resolve($eventId, 'en', $params);

        $this->assertInstanceOf(ContentView::class, $result);
        $view = $result->getView();
        $this->assertEquals($eventId, $view['id']);
        $this->assertEquals(['title', 'startDate'], $view['properties']);
    }
}