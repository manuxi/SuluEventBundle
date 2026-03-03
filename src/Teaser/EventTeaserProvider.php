<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Teaser;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\AdminBundle\Teaser\Configuration\TeaserConfiguration;
use Sulu\Bundle\AdminBundle\Teaser\Provider\TeaserProviderInterface;
use Sulu\Bundle\AdminBundle\Teaser\Teaser;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentEnhancer\ContentEnhancerInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventTeaserProvider implements TeaserProviderInterface
{
    public function __construct(
        protected EventRepository $eventRepository,
        protected ContentAggregatorInterface $contentAggregator,
        protected ContentEnhancerInterface $contentEnhancer,
        protected TranslatorInterface $translator,
    ) {
    }

    public function getConfiguration(): TeaserConfiguration
    {
        return new TeaserConfiguration(
            $this->translator->trans('sulu_event.event', [], 'admin'),
            Event::RESOURCE_KEY,
            'table',
            ['title'],
            $this->translator->trans('sulu_event.select_event', [], 'admin'),
        );
    }

    /**
     * @param array<string> $ids
     *
     * @return Teaser[]
     */
    public function find(array $ids, $locale): array
    {
        if (0 === \count($ids)) {
            return [];
        }

        $events = $this->findEventsByUuids($ids, $locale);

        $teasers = [];
        foreach ($events as $event) {
            $teaser = $this->createTeaserFromEvent($event, $locale);
            if (null !== $teaser) {
                $teasers[] = $teaser;
            }
        }

        return $teasers;
    }

    /**
     * @param array<string> $uuids
     *
     * @return array<Event>
     */
    private function findEventsByUuids(array $uuids, string $locale): array
    {
        /** @var array<Event> $events */
        $events = $this->eventRepository->findByFilters(
            filters: [
                'uuids' => $uuids,
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
            ],
            sortBys: [],
            selects: [
                EventRepository::GROUP_SELECT_EVENT_WEBSITE => true,
            ]
        );

        $uuidPositions = \array_flip($uuids);
        \usort(
            $events,
            static fn (Event $a, Event $b) => ($uuidPositions[$a->getUuid()] ?? 0) - ($uuidPositions[$b->getUuid()] ?? 0)
        );

        return $events;
    }

    private function createTeaserFromEvent(Event $event, string $locale): ?Teaser
    {
        try {
            /** @var EventDimensionContent|null $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $event,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            if (null === $dimensionContent) {
                return null;
            }

            $enhancedContent = $this->contentEnhancer->enhance($dimensionContent);
            if ($enhancedContent instanceof EventDimensionContent) {
                $dimensionContent = $enhancedContent;
            }
        } catch (ContentNotFoundException) {
            return null;
        }

        $title = $this->resolveTitle($dimensionContent);
        if (null === $title) {
            return null;
        }

        return new Teaser(
            $event->getUuid(),
            Event::RESOURCE_KEY,
            $locale,
            $title,
            $this->resolveDescription($dimensionContent),
            $this->resolveMoreText($dimensionContent),
            $this->resolveUrl($dimensionContent),
            $this->resolveMediaId($dimensionContent),
            $this->getAttributes($dimensionContent)
        );
    }

    protected function resolveUrl(EventDimensionContent $dimensionContent): ?string
    {
        $route = $dimensionContent->getRoute();
        $url = $route?->getSlug() ?? null;

        return \is_string($url) ? $url : null;
    }

    protected function resolveTitle(EventDimensionContent $dimensionContent): ?string
    {
        $title = $dimensionContent->getExcerptTitle() ?? $dimensionContent->getTitle();

        return \is_string($title) && '' !== $title ? $title : null;
    }

    protected function resolveDescription(EventDimensionContent $dimensionContent): ?string
    {
        $description = $dimensionContent->getSummary();
        if (!empty($description)) {
            return \strip_tags($description);
        }

        $text = $dimensionContent->getText();
        if (!empty($text)) {
            return \mb_substr(\strip_tags($text), 0, 200);
        }

        $excerptDescription = $dimensionContent->getExcerptDescription();
        if (!empty($excerptDescription)) {
            return \strip_tags($excerptDescription);
        }

        return null;
    }

    protected function resolveMoreText(EventDimensionContent $dimensionContent): ?string
    {
        $moreText = $dimensionContent->getExcerptMore();

        return '' !== ($moreText ?? '') ? $moreText : null;
    }

    protected function resolveMediaId(EventDimensionContent $dimensionContent): ?int
    {
        $image = $dimensionContent->getImage();
        if (null !== $image) {
            return $image->getId();
        }

        $excerptImage = $dimensionContent->getExcerptImage();
        if (isset($excerptImage['id'])) {
            return $excerptImage['id'];
        }

        $location = $dimensionContent->getLocation();
        if (null !== $location) {
            $locationImage = $location->getImage();
            if (null !== $locationImage) {
                return $locationImage->getId();
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAttributes(EventDimensionContent $dimensionContent): array
    {
        return [];
    }
}
