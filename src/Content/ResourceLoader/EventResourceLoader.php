<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\ResourceLoader;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Content\Application\ResourceLoader\Loader\ResourceLoaderInterface;

class EventResourceLoader implements ResourceLoaderInterface
{
    public const RESOURCE_LOADER_KEY = 'events';

    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    /**
     * @param string[] $ids
     * @param array<string, mixed> $params
     * @return array<string, Event>
     */
    public function load(array $ids, ?string $locale, array $params = []): array
    {
        if (empty($ids)) {
            return [];
        }

        $intIds = \array_map('intval', $ids);

        $result = $this->eventRepository->findBy(['ids' => $intIds]);

        $mappedResult = [];
        foreach ($result as $event) {
            $mappedResult[(string) $event->getId()] = $event;
        }

        return $mappedResult;
    }

    public static function getKey(): string
    {
        return self::RESOURCE_LOADER_KEY;
    }

    public static function getResourceKey(): string
    {
        return Event::RESOURCE_KEY;
    }
}