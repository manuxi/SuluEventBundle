<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\PropertyResolver;

use Manuxi\SuluEventBundle\Content\PropertyResolver\SingleEventSelectionPropertyResolver;
use Manuxi\SuluEventBundle\Content\ResourceLoader\EventResourceLoader;
use Manuxi\SuluEventBundle\Entity\Event;
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
        $result = $this->resolver->resolve('invalid', 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertNull($result->getContent());
    }

    public function testResolveWithValidId(): void
    {
        $eventId = 42;
        $result = $this->resolver->resolve($eventId, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        /*$this->assertEquals('42', $result->getContent());*/
/*        $this->assertEquals(EventResourceLoader::getKey(), $result->getResourceLoaderKey());
        $this->assertEquals(Event::RESOURCE_KEY, $result->getResourceKey());*/
        $this->assertEquals(['id' => 42], $result->getView());
/*        $this->assertEquals(150, $result->getPriority());*/
    }

    public function testResolveWithParams(): void
    {
        $eventId = 42;
        $params = ['properties' => ['title', 'startDate']];
        $result = $this->resolver->resolve($eventId, 'en', $params);

        $this->assertInstanceOf(ContentView::class, $result);
        $view = $result->getView();
        $this->assertEquals(42, $view['id']);
        $this->assertEquals(['title', 'startDate'], $view['properties']);
    }
}