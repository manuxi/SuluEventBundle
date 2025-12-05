<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EventTwigExtension extends AbstractExtension
{
    private ?EventRepository $eventRepository = null;

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

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sulu_resolve_event', [$this, 'resolveEvent']),
            new TwigFunction('sulu_get_events', [$this, 'getEvents']),
        ];
    }

    /**
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

        return $this->contentResolver->resolve($dimensionContent, $properties);
    }

    /**
     * @param array<string, string> $properties
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEvents(
        int $limit = 8,
        ?string $locale = null,
        array $properties = []
    ): array {
        if (null === $locale) {
            $localization = $this->requestAnalyzer->getCurrentLocalization();
            if (null === $localization) {
                return [];
            }
            $locale = $localization->getLocale();
        }

        $events = $this->getEventRepository()->findBy(
            [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
                'limit' => $limit,
            ]
        );

        $resolvedEvents = [];
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

            $resolvedEvents[] = $this->contentResolver->resolve($dimensionContent, $properties);
        }

        return $resolvedEvents;
    }
}