<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Trash;

use Doctrine\Common\Collections\ArrayCollection;
use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Application\Mapper\EventMapperInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\RestoredEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;
use Sulu\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Content\Application\ContentNormalizer\ContentNormalizerInterface;
use Sulu\Content\Domain\Model\DimensionContentCollection;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Webmozart\Assert\Assert;

class EventTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    /**
     * @param iterable<EventMapperInterface> $eventMappers
     */
    public function __construct(
        private readonly TrashItemRepositoryInterface $trashItemRepository,
        private readonly EventRepository $eventRepository,
        private readonly ContentNormalizerInterface $contentNormalizer,
        private readonly ContentMergerInterface $contentMerger,
        private readonly iterable $eventMappers,
        private readonly DomainEventCollectorInterface $domainEventCollector,
    ) {
    }

    public static function getResourceKey(): string
    {
        return Event::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        Assert::isInstanceOf($resource, Event::class);
        $event = $resource;

        $data = [
            'id' => $event->getId(),
        ];

        $titles = [];
        $restoreType = null;

        /** @var ArrayCollection<int, EventDimensionContent> $allDimensionContents */
        $allDimensionContents = $event->getDimensionContents();

        /** @var array<EventDimensionContent> $localizedDimensionContents */
        $localizedDimensionContents = \array_filter(
            $allDimensionContents->toArray(),
            static fn (EventDimensionContent $dimensionContent) => null !== $dimensionContent->getLocale()
                && DimensionContentInterface::STAGE_DRAFT === $dimensionContent->getStage()
                && DimensionContentInterface::CURRENT_VERSION === $dimensionContent->getVersion(),
        );
        $localizedDimensionContents = \array_combine(
            \array_map(
                static fn (EventDimensionContent $dimensionContent) => $dimensionContent->getLocale(),
                $localizedDimensionContents
            ),
            $localizedDimensionContents
        );

        $unlocalizedDimensionContent = null;
        foreach ($allDimensionContents as $dimensionContent) {
            if (null === $dimensionContent->getLocale()
                && DimensionContentInterface::STAGE_DRAFT === $dimensionContent->getStage()
                && DimensionContentInterface::CURRENT_VERSION === $dimensionContent->getVersion()
            ) {
                $unlocalizedDimensionContent = $dimensionContent;
                break;
            }
        }

        Assert::notNull($unlocalizedDimensionContent, 'Expected to find an unlocalized dimension content for the event.');
        Assert::notEmpty($localizedDimensionContents, 'Expected to find at least one localized dimension content for the event.');

        // Reorder localized dimension contents to match the order defined in availableLocales
        $availableLocales = $unlocalizedDimensionContent->getAvailableLocales();
        Assert::isArray($availableLocales, 'Expected availableLocales to be an array');
        /** @var array<string, EventDimensionContent> $localizedDimensionContents */
        $localizedDimensionContents = \array_merge(
            \array_flip(
                \array_filter(
                    $availableLocales,
                    static fn ($locale) => \array_key_exists($locale, $localizedDimensionContents)
                )
            ),
            $localizedDimensionContents,
        );

        $data['dimensionContents'] = [];
        foreach ($localizedDimensionContents as $locale => $localizedDimensionContent) {
            $mergedDimensionContent = $this->contentMerger->merge(
                new DimensionContentCollection(
                    new ArrayCollection([$unlocalizedDimensionContent, $localizedDimensionContent]),
                    [
                        'locale' => $locale,
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'version' => DimensionContentInterface::CURRENT_VERSION,
                    ],
                    EventDimensionContent::class,
                ),
            );

            $normalizedContent = $this->contentNormalizer->normalize($mergedDimensionContent);
            $data['dimensionContents'][] = $normalizedContent;

            $title = $localizedDimensionContent->getTitle();
            if ($title) {
                $titles[$locale] = $title;
            }
        }

        return $this->trashItemRepository->create(
            Event::RESOURCE_KEY,
            (string) $event->getId(),
            $titles,
            $data,
            $restoreType,
            $options,
            Event::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $restoreData = $trashItem->getRestoreData();
        $eventId = (int) $trashItem->getResourceId();

        $event = $this->eventRepository->findById($eventId);
        if (!$event) {
            $event = new Event();
            $this->eventRepository->add($event);
        }

        $dimensionContents = $restoreData['dimensionContents'] ?? [];
        $allLocales = [];
        $eventTitle = null;

        Assert::isArray($dimensionContents, 'Expected dimensionContents to be an array');
        foreach ($dimensionContents as $dimensionContentData) {
            Assert::isArray($dimensionContentData, 'Expected dimensionContentData to be an array');

            if (!$eventTitle && \array_key_exists('title', $dimensionContentData) && $dimensionContentData['title']) {
                /** @var string $eventTitle */
                $eventTitle = $dimensionContentData['title'];
            }

            if (\array_key_exists('locale', $dimensionContentData) && $dimensionContentData['locale']) {
                $allLocales[] = $dimensionContentData['locale'];
            }

            foreach ($this->eventMappers as $eventMapper) {
                $eventMapper->mapEventData($event, $dimensionContentData);
            }
        }

        $this->domainEventCollector->collect(
            new RestoredEvent($event, $restoreData)
        );

        return $event;
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(
            null,
            EventAdmin::EDIT_FORM_VIEW,
            ['id' => 'id', 'locale' => 'locale']
        );
    }
}
