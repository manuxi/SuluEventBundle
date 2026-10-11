<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Import\Resolver\MediaResolver;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

/**
 * Fills in the images that are missing: an event without an image gets the one of its first topic that has one
 * (sulu_event.import.category_images), a place without an image the one of its own (sulu_event.import.location_image).
 * Never replaces an image. The import does the same for the events and places it creates; this is for the ones that were
 * made some other way (in the admin, by the editorial work with the MCP tools, before the images were configured).
 *
 * A published event gets a draft with the image, as every change of the import: it is live after the editors publish it.
 */
class ImageFiller
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventRepository $eventRepository,
        private readonly ContentManagerInterface $contentManager,
        private readonly ContentWorkflowInterface $contentWorkflow,
        private readonly MediaResolver $mediaResolver,
        private readonly ImportSettings $settings,
        private readonly ImportUserSwitcher $userSwitcher,
    ) {
    }

    /**
     * @return array{events: list<string>, locations: list<string>, warnings: list<string>} titles of the events and names of the places that got (or, with $dryRun, would get) an image
     */
    public function fill(bool $dryRun = false): array
    {
        $fill = function () use ($dryRun): array {
            $result = ['events' => [], 'locations' => [], 'warnings' => []];
            $media  = [];

            foreach ($this->eventRepository->findAll() as $event) {
                $this->fillEvent($event, $dryRun, $media, $result);
            }
            $this->fillLocations($dryRun, $media, $result);

            if (!$dryRun) {
                $this->entityManager->flush();
            }

            return $result;
        };

        return $dryRun ? $fill() : $this->userSwitcher->runAsImportUser($fill);
    }

    /**
     * @param array<string, int|null>                                                          $media
     * @param array{events: list<string>, locations: list<string>, warnings: list<string>} $result
     */
    private function fillEvent(Event $event, bool $dryRun, array &$media, array &$result): void
    {
        $filled = false;
        foreach ($event->getDimensionContents() as $row) {
            if (!$row instanceof EventDimensionContent || DimensionContentInterface::STAGE_DRAFT !== $row->getStage() || null === $row->getLocale()) {
                continue;
            }
            if (null !== $row->getImage() || [] !== ($row->getExcerptData()['image'] ?? [])) {
                continue;
            }

            $imageId = $this->imageOfTopics($row, $media, $result);
            if (null === $imageId) {
                continue;
            }

            $filled = true;
            if ($dryRun) {
                continue;
            }

            $this->contentManager->persist(
                $event,
                [
                    'template' => $row->getTemplateKey(),
                    'image'    => $imageId,
                    // the excerpt data is replaced as a whole, so the other entries are carried along
                    'excerpt'  => array_replace($row->getExcerptData(), ['image' => ['id' => $imageId]]),
                ],
                ['locale' => $row->getLocale(), 'stage' => DimensionContentInterface::STAGE_DRAFT]
            );
            if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $row->getWorkflowPlace()) {
                $this->contentWorkflow->apply($event, ['locale' => $row->getLocale()], WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT);
            }
        }

        if ($filled) {
            $result['events'][] = $this->titleOf($event);
        }
    }

    /**
     * @param array<string, int|null>                                                          $media
     * @param array{events: list<string>, locations: list<string>, warnings: list<string>} $result
     */
    private function imageOfTopics(EventDimensionContent $row, array &$media, array &$result): ?int
    {
        foreach ($row->getExcerptCategories() as $category) {
            $reference = $this->settings->categoryImages[$category->getKey()] ?? null;
            if (null !== $reference && '' !== (string) $reference) {
                return $this->resolve($reference, $media, $result);
            }
        }

        return null;
    }

    /**
     * @param array<string, int|null>                                                          $media
     * @param array{events: list<string>, locations: list<string>, warnings: list<string>} $result
     */
    private function fillLocations(bool $dryRun, array &$media, array &$result): void
    {
        $reference = $this->settings->locationImage;
        if (null === $reference || '' === (string) $reference) {
            return;
        }

        $imageId = $this->resolve($reference, $media, $result);
        if (null === $imageId) {
            return;
        }

        foreach ($this->entityManager->getRepository(Location::class)->findAll() as $location) {
            if (null !== $location->getImage()) {
                continue;
            }

            if (!$dryRun) {
                $location->setImage($this->entityManager->getReference(Media::class, $imageId));
            }
            $result['locations'][] = (string) $location->getName();
        }
    }

    /**
     * @param array<string, int|null>                                                          $media
     * @param array{events: list<string>, locations: list<string>, warnings: list<string>} $result
     */
    private function resolve(int|string $reference, array &$media, array &$result): ?int
    {
        $key = (string) $reference;
        if (!\array_key_exists($key, $media)) {
            $media[$key] = $this->mediaResolver->resolve($reference);
            if (null === $media[$key]) {
                $result['warnings'][] = \sprintf('The image "%s" does not exist.', $reference);
            }
        }

        return $media[$key];
    }

    private function titleOf(Event $event): string
    {
        foreach ($event->getDimensionContents() as $row) {
            if ($row instanceof EventDimensionContent && null !== $row->getTitle() && '' !== $row->getTitle()) {
                return $row->getTitle();
            }
        }

        return $event->getUuid();
    }
}
