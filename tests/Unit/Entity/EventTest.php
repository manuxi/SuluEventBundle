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

    public function testGetIdReturnsNullForNewEntity(): void
    {
        $this->assertNull($this->entity->getId());
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
        $this->assertSame($this->entity, $dimensionContent->getEvent());
    }

    public function testMultipleDimensionContentsCanBeAdded(): void
    {
        // English draft
        $enDraft = new EventDimensionContent($this->entity);
        $enDraft->setLocale('en');
        $enDraft->setStage(DimensionContentInterface::STAGE_DRAFT);
        $this->entity->addDimensionContent($enDraft);

        // English live
        $enLive = new EventDimensionContent($this->entity);
        $enLive->setLocale('en');
        $enLive->setStage(DimensionContentInterface::STAGE_LIVE);
        $this->entity->addDimensionContent($enLive);

        // German draft
        $deDraft = new EventDimensionContent($this->entity);
        $deDraft->setLocale('de');
        $deDraft->setStage(DimensionContentInterface::STAGE_DRAFT);
        $this->entity->addDimensionContent($deDraft);

        // Unlocalized (for dates, location, etc.)
        $unlocalized = new EventDimensionContent($this->entity);
        $unlocalized->setLocale(null);
        $unlocalized->setStage(DimensionContentInterface::STAGE_DRAFT);
        $this->entity->addDimensionContent($unlocalized);

        $this->assertCount(4, $this->entity->getDimensionContents());
    }

}