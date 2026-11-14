<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Import\Resolver\MediaResolver;

/**
 * Which image of our own an imported event gets, from the best to the worst:
 *
 *  1. a photo of the series of the event (sulu_event.import.series_images, "ifa" => a photo of that fair),
 *  2. a photo of the place (the image of the location, if it is a real photo and not the placeholder),
 *  3. the placeholder of its first topic (sulu_event.import.category_images).
 *
 * A generated card (an image in a folder of sulu_event.import.placeholder_collections) is a placeholder, too: it gives way to
 * a photo of the series or the place and is not replaced by the placeholder of a topic (isCard).
 *
 * The images of a source are never copied (they belong to the source), and an image an editor chose is never replaced; only
 * a placeholder gives way to something better. The placeholders are the images of category_images and location_image.
 */
class EventImageChooser
{
    /** @var list<int>|null */
    private ?array $placeholders = null;

    /** @var array<int, bool> */
    private array $cards = [];

    public function __construct(
        private readonly ImportSettings $settings,
        private readonly MediaResolver $mediaResolver,
    ) {
    }

    /**
     * @param string[] $categoryKeys topics of the event
     *
     * @return int|string|null id or file name of the image
     */
    public function reference(?string $seriesKey, ?Location $location, array $categoryKeys): int|string|null
    {
        if (null !== $seriesKey && isset($this->settings->seriesImages[$seriesKey]) && '' !== (string) $this->settings->seriesImages[$seriesKey]) {
            return $this->settings->seriesImages[$seriesKey];
        }

        $photo = $location?->getImage()?->getId();
        if (null !== $photo && !$this->isPlaceholder((int) $photo)) {
            return (int) $photo;
        }

        foreach ($categoryKeys as $key) {
            if (isset($this->settings->categoryImages[$key]) && '' !== (string) $this->settings->categoryImages[$key]) {
                return $this->settings->categoryImages[$key];
            }
        }

        return null;
    }

    /**
     * A placeholder: the image of a topic or the image all places without a photo get. An event with one of them may get a
     * better image; an event with any other image keeps it.
     */
    public function isPlaceholder(int $mediaId): bool
    {
        if ($this->isCard($mediaId)) {
            return true;
        }

        if (null === $this->placeholders) {
            $references = array_values($this->settings->categoryImages);
            if (null !== $this->settings->locationImage) {
                $references[] = $this->settings->locationImage;
            }

            $this->placeholders = [];
            foreach ($references as $reference) {
                $id = '' !== (string) $reference ? $this->mediaResolver->resolve($reference) : null;
                if (null !== $id) {
                    $this->placeholders[] = $id;
                }
            }
        }

        return \in_array($mediaId, $this->placeholders, true);
    }

    /**
     * A generated card: an image in one of the folders of sulu_event.import.placeholder_collections. Better than the image of
     * a topic, worse than any photo.
     */
    public function isCard(int $mediaId): bool
    {
        if ([] === $this->settings->placeholderCollections) {
            return false;
        }

        return $this->cards[$mediaId] ??= $this->mediaResolver->isInSystemCollections($mediaId, $this->settings->placeholderCollections);
    }

    /**
     * The series of an event from the match key of its import ("ifa-2027" is the series "ifa").
     */
    public static function seriesOfMatchKey(?string $matchKey): ?string
    {
        if (null === $matchKey || 1 !== preg_match('/^(.+)-\d{4}$/', $matchKey, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
