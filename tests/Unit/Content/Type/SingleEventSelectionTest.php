<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\Type;

use Manuxi\SuluEventBundle\Content\Type\SingleEventSelection;
use Manuxi\SuluEventBundle\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Component\Content\Compat\PropertyInterface;

class SingleEventSelectionTest extends TestCase
{
    private $singleEventSelection;

    /**
     * @var ObjectProphecy<ObjectRepository<Event>>
     */
    private $eventRepository;

    protected function setUp(): void
    {
        $this->eventRepository = $this->prophesize(ObjectRepository::class);
        $entityManager         = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(Event::class)->willReturn($this->eventRepository->reveal());

        $this->singleEventSelection = new SingleEventSelection($entityManager->reveal());
    }

    public function testNullValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(null);

        $this->assertNull($this->singleEventSelection->getContentData($property->reveal()));
        $this->assertSame(['id' => null], $this->singleEventSelection->getViewData($property->reveal()));
    }

    public function testValidValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(45);

        $event45 = $this->prophesize(Event::class);

        $this->eventRepository->find(45)->willReturn($event45->reveal());

        $this->assertSame($event45->reveal(), $this->singleEventSelection->getContentData($property->reveal()));
        $this->assertSame(['id' => 45], $this->singleEventSelection->getViewData($property->reveal()));
    }
}
