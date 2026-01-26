<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class EventTest extends TestCase
{
    private Event $entity;

    protected function setUp(): void
    {
        $this->entity = new Event();
    }

    public function testGetIdReturnsUuidForNewEntity(): void
    {
        $id = $this->entity->getId();
        $this->assertIsString($id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $id
        );
    }

    public function testGetDimensionContentsReturnsEmptyCollectionForNewEntity(): void
    {
        $dimensionContents = $this->entity->getDimensionContents();

        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $dimensionContents);
        $this->assertCount(0, $dimensionContents);
    }

    public function testAddDimensionContentAddsToDimensionContents(): void
    {
        $dimensionContent = new EventDimensionContent($this->entity);
        $dimensionContent->setLocale('en');
        $dimensionContent->setStage(DimensionContentInterface::STAGE_DRAFT);

        $this->entity->addDimensionContent($dimensionContent);

        $dimensionContents = $this->entity->getDimensionContents();
        $this->assertCount(1, $dimensionContents);
        $this->assertTrue($dimensionContents->contains($dimensionContent));
    }

    public function testRemoveDimensionContentRemovesFromDimensionContents(): void
    {
        $dimensionContent = new EventDimensionContent($this->entity);
        $dimensionContent->setLocale('en');
        $dimensionContent->setStage(DimensionContentInterface::STAGE_DRAFT);

        $this->entity->addDimensionContent($dimensionContent);
        $this->assertCount(1, $this->entity->getDimensionContents());

        $this->entity->removeDimensionContent($dimensionContent);
        $this->assertCount(0, $this->entity->getDimensionContents());
    }

    public function testCreateDimensionContentCreatesNewDimensionContent(): void
    {
        $dimensionContent = $this->entity->createDimensionContent();

        $this->assertInstanceOf(EventDimensionContent::class, $dimensionContent);
        $this->assertSame($this->entity, $dimensionContent->getResource());
    }

    public function testGetResourceKeyReturnsCorrectValue(): void
    {
        $this->assertEquals('events', Event::RESOURCE_KEY);
    }

    public function testGetSecurityContextReturnsCorrectValue(): void
    {
        $this->assertEquals('sulu.events.events', Event::SECURITY_CONTEXT);
    }
}