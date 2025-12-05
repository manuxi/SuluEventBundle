<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\ResourceLoader;

use Manuxi\SuluEventBundle\Content\ResourceLoader\EventResourceLoader;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use PHPUnit\Framework\TestCase;

class EventResourceLoaderTest extends TestCase
{
    private EventRepository $repository;
    private EventResourceLoader $resourceLoader;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EventRepository::class);
        $this->resourceLoader = new EventResourceLoader($this->repository);
    }

    public function testGetKey(): void
    {
        $this->assertEquals(EventResourceLoader::RESOURCE_LOADER_KEY, EventResourceLoader::getKey());
    }

    public function testGetResourceKey(): void
    {
        $this->assertEquals('events', EventResourceLoader::getResourceKey());
    }

}