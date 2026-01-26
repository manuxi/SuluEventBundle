<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Reference;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Bundle\ReferenceBundle\Application\Collector\ReferenceCollector;
use Sulu\Bundle\ReferenceBundle\Application\Refresh\ReferenceRefresherInterface;
use Sulu\Bundle\ReferenceBundle\Domain\Repository\ReferenceRepositoryInterface;
use Sulu\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Content\Application\ContentResolver\ContentViewResolver\ContentViewResolverInterface;
use Sulu\Content\Domain\Model\DimensionContentCollection;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class EventReferenceRefresher implements ReferenceRefresherInterface
{
    /**
     * @var EntityRepository<EventDimensionContent>
     */
    private EntityRepository $eventDimensionContentRepository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReferenceRepositoryInterface $referenceRepository,
        private readonly ContentViewResolverInterface $contentViewResolver,
        private readonly ContentMergerInterface $contentMerger,
    ) {
        /** @var EntityRepository<EventDimensionContent> $repository */
        $repository = $this->entityManager->getRepository(EventDimensionContent::class);
        $this->eventDimensionContentRepository = $repository;
    }

    public static function getResourceKey(): string
    {
        return Event::RESOURCE_KEY;
    }

    public function refresh(?array $filter = null): \Generator
    {
        $eventDimensionContentsGenerator = $this->getEventDimensionContentsGenerator($filter);

        $currentResourceId = null;
        $currentGroup = [];

        /** @var EventDimensionContent $dimensionContent */
        foreach ($eventDimensionContentsGenerator as $dimensionContent) {
            $resourceId = $dimensionContent->getResource()->getId();

            if (null === $currentResourceId) {
                $currentResourceId = $resourceId;
            }

            if ($resourceId !== $currentResourceId) {
                foreach ($this->resolveEventDimensionContents($currentGroup) as $merged) {
                    $this->processEventDimensionContent($merged);
                    yield $merged;
                }

                $currentGroup = [];
                $currentResourceId = $resourceId;
            }

            $currentGroup[] = $dimensionContent;
        }

        if ([] !== $currentGroup) {
            foreach ($this->resolveEventDimensionContents($currentGroup) as $merged) {
                $this->processEventDimensionContent($merged);
                yield $merged;
            }
        }
    }

    private function processEventDimensionContent(EventDimensionContent $eventDimensionContent): void
    {
        $referenceCollector = new ReferenceCollector(
            referenceRepository: $this->referenceRepository,
            referenceResourceKey: $eventDimensionContent->getResourceKey(),
            referenceResourceId: (string) $eventDimensionContent->getResource()->getId(),
            referenceLocale: $eventDimensionContent->getLocale() ?? '',
            referenceTitle: $eventDimensionContent->getTitle() ?? '',
            referenceContext: $eventDimensionContent->getStage(),
            referenceRouterAttributes: [
                'locale' => $eventDimensionContent->getLocale() ?? '',
            ]
        );

        $contentViews = $this->contentViewResolver->getContentViews(dimensionContent: $eventDimensionContent);

        foreach ($contentViews as $key => $contentView) {
            $basePath = 'template' !== $key ? (string) $key : '';
            $references = $contentView->getAllReferencesRecursively($basePath);

            foreach ($references as $reference) {
                $referenceCollector->addReference(
                    $reference->getResourceKey(),
                    (string) $reference->getResourceId(),
                    $reference->getPath()
                );
            }
        }

        $this->collectDirectReferences($eventDimensionContent, $referenceCollector);

        $referenceCollector->persistReferences();
    }

    private function collectDirectReferences(
        EventDimensionContent $eventDimensionContent,
        ReferenceCollector $referenceCollector
    ): void {
        $image = $eventDimensionContent->getImage();
        if (null !== $image) {
            $referenceCollector->addReference(
                'media',
                (string) $image->getId(),
                'image'
            );
        }

        $pdf = $eventDimensionContent->getPdf();
        if (null !== $pdf) {
            $referenceCollector->addReference(
                'media',
                (string) $pdf->getId(),
                'pdf'
            );
        }

        $images = $eventDimensionContent->getImages();
        if (null !== $images && \is_array($images)) {
            foreach ($images as $index => $imageData) {
                if (isset($imageData['id'])) {
                    $referenceCollector->addReference(
                        'media',
                        (string) $imageData['id'],
                        'images[' . $index . ']'
                    );
                }
            }
        }

        $speaker = $eventDimensionContent->getSpeaker();
        if (null !== $speaker) {
            $referenceCollector->addReference(
                'contacts',
                (string) $speaker->getId(),
                'speaker'
            );
        }

        $author = $eventDimensionContent->getAuthor();
        if (null !== $author) {
            $referenceCollector->addReference(
                'contacts',
                (string) $author->getId(),
                'author'
            );
        }

        $location = $eventDimensionContent->getLocation();
        if (null !== $location) {
            $referenceCollector->addReference(
                'locations',
                (string) $location->getId(),
                'location'
            );
        }
    }

    /**
     * @param array{
     *      resourceId: string,
     *      resourceKey: string,
     *      locale: string,
     *      stage: string
     *  }|null $filter
     *
     * @return iterable<EventDimensionContent>
     */
    private function getEventDimensionContentsGenerator(?array $filter = null): iterable
    {
        $queryBuilder = $this->eventDimensionContentRepository->createQueryBuilder('dimensionContent')
            ->where('dimensionContent.version = :version')
            ->setParameter('version', DimensionContentInterface::CURRENT_VERSION)
            ->orderBy('dimensionContent.event', 'ASC');

        if (null !== $filter) {
            $queryBuilder
                ->join(
                    'dimensionContent.event',
                    'event',
                    Join::WITH,
                    'event.uuid = :resourceId'
                )
                ->andWhere('dimensionContent.locale = :locale OR dimensionContent.locale IS NULL')
                ->andWhere('dimensionContent.stage = :stage')
                ->setParameter('resourceId', $filter['resourceId'])
                ->setParameter('locale', $filter['locale'])
                ->setParameter('stage', $filter['stage']);
        }

        /** @var iterable<EventDimensionContent> $result */
        $result = $queryBuilder->getQuery()->toIterable();

        return $result;
    }

    /**
     * @param iterable<EventDimensionContent> $eventDimensionContents
     *
     * @return \Generator<EventDimensionContent>
     */
    private function resolveEventDimensionContents(iterable $eventDimensionContents): \Generator
    {
        $groupedEventDimensionContents = [];

        /** @var EventDimensionContent $eventDimensionContent */
        foreach ($eventDimensionContents as $eventDimensionContent) {
            $resourceId = $eventDimensionContent->getResource()->getId();
            $stage = $eventDimensionContent->getStage();
            $locale = $eventDimensionContent->getLocale();

            $groupedEventDimensionContents[$resourceId][$stage][$locale] = $eventDimensionContent;
        }

        foreach ($groupedEventDimensionContents as $eventDimensionContentByStage) {
            foreach ($eventDimensionContentByStage as $stage => $eventDimensionContentByLocale) {
                $unlocalizedDimensionContent = $eventDimensionContentByLocale[null] ?? null;

                foreach ($eventDimensionContentByLocale as $locale => $localizedDimensionContent) {
                    if (null === $locale) {
                        continue;
                    }

                    if (null !== $unlocalizedDimensionContent) {
                        $dimensionContentCollection = new DimensionContentCollection(
                            new ArrayCollection([$unlocalizedDimensionContent, $localizedDimensionContent]),
                            [
                                'locale' => $locale,
                                'stage' => $stage,
                            ],
                            EventDimensionContent::class
                        );

                        /** @var EventDimensionContent $mergedDimensionContent */
                        $mergedDimensionContent = $this->contentMerger->merge($dimensionContentCollection);

                        if (null === $mergedDimensionContent->getLocale()) {
                            $mergedDimensionContent->setLocale($locale);
                        }

                        if (empty($mergedDimensionContent->getTemplateKey())) {
                            $mergedDimensionContent->setTemplateKey('event');
                        }

                        yield $mergedDimensionContent;
                    } else {
                        yield $localizedDimensionContent;
                    }
                }
            }
        }
    }
}
