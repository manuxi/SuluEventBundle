<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EventTwigExtension extends AbstractExtension
{
    private ?EventRepository $eventRepository = null;
    private ?LocationRepository $locationRepository = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly ContentResolverInterface $contentResolver,
        private readonly RequestAnalyzerInterface $requestAnalyzer,
    ) {
    }

    private function getEventRepository(): EventRepository
    {
        if (null === $this->eventRepository) {
            $repository = $this->entityManager->getRepository(Event::class);

            if (!$repository instanceof EventRepository) {
                throw new \RuntimeException(sprintf('Expected EventRepository, got %s', get_class($repository)));
            }

            $this->eventRepository = $repository;
        }

        return $this->eventRepository;
    }

    private function getLocationRepository(): LocationRepository
    {
        if (null === $this->locationRepository) {
            $repository = $this->entityManager->getRepository(Location::class);
            if (!$repository instanceof LocationRepository) {
                throw new \RuntimeException(sprintf('Expected LocationRepository, got %s', \get_class($repository)));
            }
            $this->locationRepository = $repository;
        }

        return $this->locationRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sulu_resolve_location', [$this, 'resolveLocation']),
            new TwigFunction('sulu_resolve_event', [$this, 'resolveEvent']),
            new TwigFunction('sulu_get_events', [$this, 'getEvents']),
        ];
    }

    public function resolveLocation(int $id): ?Location
    {
        return $this->getLocationRepository()->find($id);
    }

    /**
     * Resolve a single event by ID.
     *
     * @param array<string, string> $properties
     *
     * @return array<string, mixed>|null
     */
    public function resolveEvent(int $id, array $properties = [], ?string $locale = null): ?array
    {
        if (null === $locale) {
            $localization = $this->requestAnalyzer->getCurrentLocalization();
            if (null === $localization) {
                return null;
            }
            $locale = $localization->getLocale();
        }

        $event = $this->getEventRepository()->findOneBy(['id' => $id]);
        if (!$event) {
            return null;
        }

        /** @var EventDimensionContent $dimensionContent */
        $dimensionContent = $this->contentAggregator->aggregate(
            $event,
            [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
                'version' => DimensionContentInterface::CURRENT_VERSION,
            ]
        );

        if (!$dimensionContent->getTitle()) {
            return null;
        }

        return $this->contentResolver->resolve($dimensionContent, $properties);
    }

    /**
     * Get multiple events with filters.
     *
     * @param array<string, mixed>  $filters
     * @param array<string, string> $properties
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEvents(
        array $filters = [],
        array $properties = [],
        ?string $locale = null,
        int $limit = 10,
    ): array {
        if (null === $locale) {
            $localization = $this->requestAnalyzer->getCurrentLocalization();
            if (null === $localization) {
                return [];
            }
            $locale = $localization->getLocale();
        }

        $filters['locale'] = $locale;
        $filters['stage'] = DimensionContentInterface::STAGE_LIVE;
        $filters['limit'] = $limit;

        $events = $this->getEventRepository()->findByFilters($filters);
        $result = [];

        foreach ($events as $event) {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $event,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            if (!$dimensionContent->getTitle()) {
                continue;
            }

            $result[] = $this->contentResolver->resolve($dimensionContent, $properties);
        }

        return $result;
    }
}
