<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FeedController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    #[Route('/events/feed.{_format}', name: 'sulu_event.feed', requirements: ['_format' => 'rss|atom'])]
    public function feedAction(string $_locale, string $_format): Response
    {
        $events = $this->eventRepository->findAll();

        // Resolve dimension content for each event
        $resolvedEvents = [];
        foreach ($events as $event) {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $event,
                [
                    'locale' => $_locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            // Only published events
            if ($dimensionContent->getWorkflowPlace() !== WorkflowInterface::WORKFLOW_PLACE_PUBLISHED) {
                continue;
            }

            // Skip if no title
            if (!$dimensionContent->getTitle()) {
                continue;
            }

            $resolvedEvents[] = [
                'event' => $event,
                'content' => $dimensionContent,
            ];
        }

        // Limit to 50 most recent
        $resolvedEvents = \array_slice($resolvedEvents, 0, 50);

        $feed = match ($_format) {
            'rss' => $this->generateRss($resolvedEvents, $_locale),
            'atom' => $this->generateAtom($resolvedEvents, $_locale),
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
     *
     * @param array<int, array{event: \Manuxi\SuluEventBundle\Entity\Event, content: EventDimensionContent}> $resolvedEvents
     */
    private function generateRss(array $resolvedEvents, string $locale): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"/>');
        $channel = $xml->addChild('channel');

        $channel->addChild('title', 'Events Feed');
        $channel->addChild('link', $this->urlGenerator->generate('sulu_event.feed', ['_format' => 'rss', '_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL));
        $channel->addChild('description', 'Latest events');
        $channel->addChild('language', $locale);

        // Add atom:link for self-reference
        $atomLink = $channel->addChild('atom:link', '', 'http://www.w3.org/2005/Atom');
        $atomLink->addAttribute('href', $this->urlGenerator->generate('sulu_event.feed', ['_format' => 'rss', '_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL));
        $atomLink->addAttribute('rel', 'self');
        $atomLink->addAttribute('type', 'application/rss+xml');

        foreach ($resolvedEvents as $resolved) {
            $event = $resolved['event'];
            $content = $resolved['content'];

            $item = $channel->addChild('item');
            $item->addChild('title', htmlspecialchars($content->getTitle() ?? '', ENT_XML1));
            $item->addChild('link', htmlspecialchars($content->getUrl() ?? '', ENT_XML1));
            $item->addChild('description', htmlspecialchars($content->getSummary() ?? '', ENT_XML1));

            if ($content->isPublished() && $content->getWorkflowPublished()) {
                $item->addChild('pubDate', $content->getWorkflowPublished()->format(\DATE_RSS));
            }

            $item->addChild('guid', (string) $event->getId());
        }

        return $xml->asXML();
    }

    /**
     * Generate Atom 1.0 feed.
     *
     * @param array<int, array{event: \Manuxi\SuluEventBundle\Entity\Event, content: EventDimensionContent}> $resolvedEvents
     */
    private function generateAtom(array $resolvedEvents, string $locale): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><feed xmlns="http://www.w3.org/2005/Atom"/>');

        $xml->addChild('title', 'Events Feed');
        $xml->addChild('updated', gmdate(\DATE_ATOM));

        $link = $xml->addChild('link');
        $link->addAttribute('href', $this->urlGenerator->generate('sulu_event.feed', ['_format' => 'atom', '_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL));
        $link->addAttribute('rel', 'self');

        $xml->addChild('id', $this->urlGenerator->generate('sulu_event.feed', ['_format' => 'atom', '_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL));

        foreach ($resolvedEvents as $resolved) {
            $event = $resolved['event'];
            $content = $resolved['content'];

            $entry = $xml->addChild('entry');
            $entry->addChild('title', htmlspecialchars($content->getTitle() ?? '', ENT_XML1));
            $entry->addChild('id', (string) $event->getId());

            $entryLink = $entry->addChild('link');
            $entryLink->addAttribute('href', htmlspecialchars($content->getUrl() ?? '', ENT_XML1));

            if ($content->getSummary()) {
                $entry->addChild('summary', htmlspecialchars($content->getSummary(), ENT_XML1));
            }

            if ($content->isPublished() && $content->getWorkflowPublished()) {
                $published = $content->getWorkflowPublished()->format(\DATE_ATOM);
                $entry->addChild('published', $published);
                $entry->addChild('updated', $published);
            }
        }

        return $xml->asXML();
    }
}