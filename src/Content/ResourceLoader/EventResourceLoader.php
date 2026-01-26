<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\ResourceLoader;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Content\Application\ResourceLoader\Loader\ResourceLoaderInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

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

        $stage = $params['stage'] ?? DimensionContentInterface::STAGE_LIVE;
        $result = $this->eventRepository->findByUuids($ids, $locale, $stage);

        $mappedResult = [];
        foreach ($result as $event) {
            $mappedResult[$event->getUuid()] = $event;
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
