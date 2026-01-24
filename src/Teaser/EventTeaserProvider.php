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

        $events = $this->findEventsByIds($ids, $locale);

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
     * @param array<string> $ids
     *
     * @return array<Event>
     */
    private function findEventsByIds(array $ids, string $locale): array
    {
        $intIds = \array_map(static fn ($id) => (int) $id, $ids);

        /** @var array<Event> $events */
        $events = $this->eventRepository->findByFilters(
            filters: [
                'ids' => $intIds,
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
            ],
            sortBys: [],
            selects: [
                EventRepository::GROUP_SELECT_EVENT_WEBSITE => true,
            ]
        );

        // Sort by original order
        $idPositions = \array_flip($ids);
        \usort(
            $events,
            static fn (Event $a, Event $b) => ($idPositions[(string) $a->getId()] ?? 0) - ($idPositions[(string) $b->getId()] ?? 0)
        );

        return $events;
    }

    private function createTeaserFromEvent(Event $event, string $locale): ?Teaser
    {
        $dimensionContent = $this->resolveDimensionContent($event, $locale);
        if (null === $dimensionContent) {
            return null;
        }

        /** @var EventDimensionContent $dimensionContent */
        $dimensionContent = $this->contentEnhancer->enhance($dimensionContent);

        $url = $this->resolveUrl($dimensionContent);

        /** @var string|null $title */
        $title = $this->resolveTitle($dimensionContent);

        if (null === $title) {
            return null;
        }

        /** @var string $description */
        $description = $this->resolveDescription($dimensionContent); // @phpstan-ignore-line

        /** @var string $moreText */
        $moreText = $this->resolveMoreText($dimensionContent); // @phpstan-ignore-line

        /** @var int $mediaId */
        $mediaId = $this->resolveMediaId($dimensionContent);

        $teaser = new Teaser(
            (string) $event->getId(),
            Event::RESOURCE_KEY,
            $locale,
            $title,
            $description ?? '',
            $moreText ?? '',
            $url ?? '',
            $mediaId,
            $this->getAttributes($dimensionContent),
        );

        return $teaser;
    }

    protected function resolveDimensionContent(Event $event, string $locale): ?EventDimensionContent
    {
        try {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate($event, [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
            ]);
        } catch (ContentNotFoundException) {
            return null;
        }

        return $dimensionContent;
    }

    protected function resolveUrl(EventDimensionContent $dimensionContent): ?string
    {
        $route = $dimensionContent->getRoute();
        if (null !== $route) {
            return $route->getSlug();
        }

        $templateData = $dimensionContent->getTemplateData();
        $url = $templateData['url'] ?? null;

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

        return $excerptImage['id'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAttributes(EventDimensionContent $dimensionContent): array
    {
        return [];
    }
}