<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\ResourceLoader;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Component\Serializer\ArraySerializerInterface;
use Sulu\Content\Application\ResourceLoader\Loader\ResourceLoaderInterface;

class EventResourceLoader implements ResourceLoaderInterface
{
    public const RESOURCE_LOADER_KEY = 'events';

    public function __construct(
        private EventRepository $eventRepository,
        private ArraySerializerInterface $serializer,
    ) {
    }

    /**
     * @param array<string> $ids
     * @param array<string, mixed> $params
     * @return array<array<string, mixed>>
     */
    public function load(array $ids, ?string $locale, array $params = []): array
    {
        if (empty($ids)) {
            return [];
        }

        $intIds = array_map('intval', $ids);

        $events = $this->eventRepository->findBy(['id' => $intIds]);

        // Build associative array by ID
        $eventsById = [];
        foreach ($events as $event) {
            $event->setLocale($locale);
            $eventsById[$event->getId()] = $event;
        }

        $result = [];
        foreach ($ids as $id) {  // Use STRING IDs from input!
            $intId = (int) $id;
            if (isset($eventsById[$intId])) {
                $serialized = $this->serializer->serialize($eventsById[$intId], null);
                // Ensure id is string
                $serialized['id'] = $id;  // Keep as string
                $result[$id] = $serialized;  // Use STRING ID as key!
            }
        }

        return $result;
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