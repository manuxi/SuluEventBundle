<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Import;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Tests\Integration\Stub\StubCategory;
use Manuxi\SuluEventBundle\Tests\Integration\Stub\StubTag;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

/**
 * Stands in for the content manager of Sulu (templates, routes, mappers are not part of the import): writes the
 * data into the draft rows of the event the way the mappers of the bundle do, and remembers every call.
 */
class FakeContentManager implements ContentManagerInterface
{
    /** @var list<array{locale: string, data: array<string, mixed>}> */
    public array $calls = [];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function resolve(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): DimensionContentInterface
    {
        throw new \LogicException('not used by the import');
    }

    public function persist(ContentRichEntityInterface $contentRichEntity, array $data, array $dimensionAttributes): DimensionContentInterface
    {
        \assert($contentRichEntity instanceof Event);
        $locale        = $dimensionAttributes['locale'];
        $this->calls[] = ['locale' => $locale, 'data' => $data];

        $row = null;
        foreach ($contentRichEntity->getDimensionContents() as $existing) {
            if (DimensionContentInterface::STAGE_DRAFT === $existing->getStage() && $locale === $existing->getLocale()) {
                $row = $existing;
            }
        }

        if (null === $row) {
            $row = new EventDimensionContent($contentRichEntity);
            $row->setStage(DimensionContentInterface::STAGE_DRAFT);
            $row->setLocale($locale);
            $row->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED);
            $contentRichEntity->addDimensionContent($row);
            $this->entityManager->persist($row);
        }

        foreach (['title' => 'setTitle', 'subtitle' => 'setSubtitle', 'summary' => 'setSummary', 'text' => 'setText', 'type' => 'setType'] as $key => $setter) {
            if (\array_key_exists($key, $data)) {
                $row->{$setter}($data[$key]);
            }
        }
        foreach (['startDate' => 'setStartDate', 'endDate' => 'setEndDate'] as $key => $setter) {
            if (\array_key_exists($key, $data)) {
                $row->{$setter}($data[$key] ? new \DateTimeImmutable($data[$key]) : null);
            }
        }
        if (\array_key_exists('locationId', $data)) {
            $row->setLocation(null !== $data['locationId'] ? $this->entityManager->find(Location::class, $data['locationId']) : null);
        }
        if (\array_key_exists('excerptCategories', $data)) {
            $row->setExcerptCategories(array_map(fn (int $id) => $this->entityManager->find(StubCategory::class, $id), $data['excerptCategories']));
        }
        if (\array_key_exists('excerptTags', $data)) {
            $row->setExcerptTags(array_map(fn (int $id) => $this->entityManager->find(StubTag::class, $id), $data['excerptTags']));
        }

        if (\array_key_exists('excerpt', $data)) {
            $row->setExcerptData($data['excerpt']);
        }

        $row->applyUnlocalizedDetails($data);
        $row->applyLocalizedDetails($data);
        $row->normalizeAllDayTimes();

        return $row;
    }

    public function normalize(DimensionContentInterface $dimensionContent): array
    {
        return [];
    }

    public function copy(
        ContentRichEntityInterface $sourceContentRichEntity,
        array $sourceDimensionAttributes,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes,
        array $options = [],
    ): DimensionContentInterface {
        throw new \LogicException('not used by the import');
    }

    public function applyTransition(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes, string $transitionName): DimensionContentInterface
    {
        throw new \LogicException('not used by the import');
    }
}
