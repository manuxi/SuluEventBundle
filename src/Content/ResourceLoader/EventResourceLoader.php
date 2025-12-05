<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\ResourceLoader;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Content\Application\ResourceLoader\Loader\ResourceLoaderInterface;

class EventResourceLoader implements ResourceLoaderInterface
{
    public const RESOURCE_LOADER_KEY = 'events';

    private ?EventRepository $eventRepository = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    private function getEventRepository(): EventRepository
    {
        if (null === $this->eventRepository) {
            $repository = $this->entityManager->getRepository(Event::class);

            // This should be our EventRepository because Event.orm.xml declares it
            if (!$repository instanceof EventRepository) {
                throw new \RuntimeException(
                    sprintf(
                        'Expected EventRepository, got %s',
                        get_class($repository)
                    )
                );
            }

            $this->eventRepository = $repository;
        }

        return $this->eventRepository;
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

        $result = $this->getEventRepository()->findBy(['ids' => $intIds]);

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