<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Trash;

use Doctrine\Common\Collections\ArrayCollection;
use Manuxi\SuluEventBundle\Domain\Event\Event\RestoredEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Trash\EventTrashItemHandler;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;
use Sulu\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Content\Application\ContentNormalizer\ContentNormalizerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class EventTrashItemHandlerTest extends TestCase
{
    private EventTrashItemHandler $handler;
    private TrashItemRepositoryInterface $trashItemRepository;
    private EventRepository $eventRepository;
    private ContentNormalizerInterface $contentNormalizer;
    private ContentMergerInterface $contentMerger;
    private DomainEventCollectorInterface $domainEventCollector;

    protected function setUp(): void
    {
        $this->trashItemRepository = $this->createMock(TrashItemRepositoryInterface::class);
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->contentNormalizer = $this->createMock(ContentNormalizerInterface::class);
        $this->contentMerger = $this->createMock(ContentMergerInterface::class);
        $this->domainEventCollector = $this->createMock(DomainEventCollectorInterface::class);

        $this->handler = new EventTrashItemHandler(
            $this->trashItemRepository,
            $this->eventRepository,
            $this->contentNormalizer,
            $this->contentMerger,
            new \ArrayIterator([]), // eventMappers
            $this->domainEventCollector
        );
    }

    public function testStore(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn(1);

        $unlocalizedContent = $this->createMock(EventDimensionContent::class);
        $unlocalizedContent->method('getLocale')->willReturn(null);
        $unlocalizedContent->method('getStage')->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $unlocalizedContent->method('getVersion')->willReturn(DimensionContentInterface::CURRENT_VERSION);
        $unlocalizedContent->method('getAvailableLocales')->willReturn(['en']);

        $localizedContent = $this->createMock(EventDimensionContent::class);
        $localizedContent->method('getLocale')->willReturn('en');
        $localizedContent->method('getStage')->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $localizedContent->method('getVersion')->willReturn(DimensionContentInterface::CURRENT_VERSION);
        $localizedContent->method('getTitle')->willReturn('Trash Title');

        $allContents = new ArrayCollection([$unlocalizedContent, $localizedContent]);
        $event->method('getDimensionContents')->willReturn($allContents);

        // Expect merger to be called
        $mergedContent = $this->createMock(EventDimensionContent::class);
        $this->contentMerger->method('merge')->willReturn($mergedContent);

        // Expect normalizer
        $normalizedContent = ['some' => 'normalized', 'data' => 'here'];
        $this->contentNormalizer->method('normalize')->willReturn($normalizedContent);

        // Expect creation
        $trashItem = $this->createMock(TrashItemInterface::class);
        $this->trashItemRepository->expects($this->once())
            ->method('create')
            ->with(
                Event::RESOURCE_KEY,
                '1',
                ['en' => 'Trash Title'],
                $this->callback(function ($data) use ($normalizedContent) {
                    return 1 === $data['id']
                        && isset($data['dimensionContents'])
                        && in_array($normalizedContent, $data['dimensionContents']);
                })
            )
            ->willReturn($trashItem);

        $result = $this->handler->store($event);
        $this->assertSame($trashItem, $result);
    }

    public function testRestore(): void
    {
        $trashItem = $this->createMock(TrashItemInterface::class);
        $trashItem->method('getResourceId')->willReturn('1');
        $trashItem->method('getRestoreData')->willReturn([
            'dimensionContents' => [],
        ]);

        $event = $this->createMock(Event::class);
        $this->eventRepository->method('findById')->with(1)->willReturn($event);

        $this->domainEventCollector->expects($this->once())
            ->method('collect')
            ->with($this->isInstanceOf(RestoredEvent::class));

        $result = $this->handler->restore($trashItem, []);
        $this->assertSame($event, $result);
    }
}
