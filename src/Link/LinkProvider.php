<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Link;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfigurationBuilder;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkItem;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkProviderInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LinkProvider implements LinkProviderInterface
{
    public function __construct(
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly EventRepository $eventRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getConfigurationBuilder(): LinkConfigurationBuilder
    {
        return LinkConfigurationBuilder::create()
            ->setTitle($this->translator->trans('sulu_event.event', [], 'admin'))
            ->setResourceKey(Event::RESOURCE_KEY)
            ->setListAdapter('table')
            ->setDisplayProperties(['title'])
            ->setOverlayTitle($this->translator->trans('sulu_event.event', [], 'admin'))
            ->setEmptyText($this->translator->trans('sulu_event.empty_list', [], 'admin'))
            ->setIcon('su-calendar');
    }

    public function preload(array $hrefs, string $locale, bool $published = true): iterable
    {
        if (0 === \count($hrefs)) {
            return [];
        }

        $dimensionAttributes = [
            'locale' => $locale,
            'stage' => $published ? DimensionContentInterface::STAGE_LIVE : DimensionContentInterface::STAGE_DRAFT,
        ];

        $intIds = \array_map('intval', $hrefs);
        $events = $this->eventRepository->findBy(['ids' => $intIds]);

        foreach ($events as $event) {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate($event, $dimensionAttributes);

            $title = $dimensionContent->getTitle() ?? '';
            $url = $dimensionContent->getRoute()?->getSlug() ?? '';
            $isPublished = $dimensionContent->getWorkflowPlace() === WorkflowInterface::WORKFLOW_PLACE_PUBLISHED;

            yield new LinkItem((string) $event->getId(), $title, $url, $isPublished);
        }
    }
}