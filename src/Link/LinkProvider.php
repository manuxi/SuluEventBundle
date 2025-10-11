<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Link;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfiguration;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfigurationBuilder;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkItem;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LinkProvider implements LinkProviderInterface
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getConfiguration(): LinkConfiguration
    {
        return LinkConfigurationBuilder::create()
            ->setTitle($this->translator->trans('sulu_event.event', [], 'admin'))
            ->setResourceKey(Event::RESOURCE_KEY)
            ->setListAdapter('table')
            ->setDisplayProperties(['title'])
            ->setOverlayTitle($this->translator->trans('sulu_event.event', [], 'admin'))
            ->setEmptyText($this->translator->trans('sulu_event.empty_list', [], 'admin'))
            ->setIcon('su-calendar')
            ->getLinkConfiguration();
    }

    public function preload(array $hrefs, $locale, $published = true): array
    {
        if (0 === count($hrefs)) {
            return [];
        }

        $result = [];
        $elements = $this->eventRepository->findBy(['id' => $hrefs]); // load items by id
        foreach ($elements as $element) {
            $element->setLocale($locale);
            $result[] = new LinkItem($element->getId(), $element->getTitle(), $element->getRoutePath(), $element->isPublished()); // create link-item foreach item
        }

        return $result;
    }
}
