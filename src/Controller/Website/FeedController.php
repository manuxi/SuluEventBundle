<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FeedController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/events/feed.{_format}', name: 'sulu_event.feed', requirements: ['_format' => 'rss|atom'])]
    public function feedAction(string $_locale, string $_format): Response
    {
        // Use the correct repository method name with locale
        $events = $this->eventRepository->findForFeed($_locale, 50);

        $feed = match ($_format) {
            'rss' => $this->generateRss($events),
            'atom' => $this->generateAtom($events),
            default => throw new \InvalidArgumentException('Unsupported format'),
        };

        return new Response(
            $feed,
            Response::HTTP_OK,
            ['Content-Type' => "application/{$_format}+xml; charset=utf-8"]
        );
    }

    /**
     * Generate RSS 2.0 feed.
     */
    private function generateRss(array $events): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"/>');
        $channel = $xml->addChild('channel');

        $channel->addChild('title', 'Events Feed');
        $channel->addChild('link', $this->urlGenerator->generate('sulu_event.feed', ['_format' => 'rss'], UrlGeneratorInterface::ABSOLUTE_URL));
        $channel->addChild('description', 'Latest events');
        $channel->addChild('language', 'de');

        // Add atom:link for self-reference
        $atomLink = $channel->addChild('atom:link', '', 'http://www.w3.org/2005/Atom');
        $atomLink->addAttribute('href', $this->urlGenerator->generate('sulu_event.feed', ['_format' => 'rss'], UrlGeneratorInterface::ABSOLUTE_URL));
        $atomLink->addAttribute('rel', 'self');
        $atomLink->addAttribute('type', 'application/rss+xml');

        foreach ($events as $event) {
            $item = $channel->addChild('item');
            $item->addChild('title', htmlspecialchars($event->getTitle(), ENT_XML1));
            $item->addChild('link', htmlspecialchars($event->getRoutePath(), ENT_XML1));
            $item->addChild('description', htmlspecialchars($event->getSummary() ?? '', ENT_XML1));

            if ($event->getPublished()) {
                $item->addChild('pubDate', $event->getPublishedAt()?->format(\DATE_RSS) ?? '');
            }

            $item->addChild('guid', (string) $event->getId());
        }

        return $xml->asXML();
    }

    /**
     * Generate Atom 1.0 feed.
     */
    private function generateAtom(array $events): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><feed xmlns="http://www.w3.org/2005/Atom"/>');

        $xml->addChild('title', 'Events Feed');
        $xml->addChild('updated', gmdate(\DATE_ATOM));

        $link = $xml->addChild('link');
        $link->addAttribute('href', $this->urlGenerator->generate('sulu_event.feed', ['_format' => 'atom'], UrlGeneratorInterface::ABSOLUTE_URL));
        $link->addAttribute('rel', 'self');

        $xml->addChild('id', $this->urlGenerator->generate('sulu_event.feed', ['_format' => 'atom'], UrlGeneratorInterface::ABSOLUTE_URL));

        foreach ($events as $event) {
            $entry = $xml->addChild('entry');
            $entry->addChild('title', htmlspecialchars($event->getTitle(), ENT_XML1));
            $entry->addChild('id', (string) $event->getId());

            $entryLink = $entry->addChild('link');
            $entryLink->addAttribute('href', htmlspecialchars($event->getRoutePath(), ENT_XML1));

            if ($event->getSummary()) {
                $entry->addChild('summary', htmlspecialchars($event->getSummary(), ENT_XML1));
            }

            if ($event->getPublished() && $event->getPublishedAt()) {
                $entry->addChild('published', $event->getPublishedAt()->format(\DATE_ATOM));
                $entry->addChild('updated', $event->getPublishedAt()->format(\DATE_ATOM));
            }
        }

        return $xml->asXML();
    }
}