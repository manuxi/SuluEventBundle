<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\Normalizer;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Content\Application\ContentNormalizer\Normalizer\NormalizerInterface;

class EventNormalizer implements NormalizerInterface
{
    public function getIgnoredAttributes(object $object): array
    {
        if (!$object instanceof EventDimensionContent) {
            return [];
        }

        return [
            'event',
            'image',
            'pdf',
            'speaker',
            'author',
            'location',
        ];
    }

    public function enhance(object $object, array $normalizedData): array
    {
        if (!$object instanceof EventDimensionContent) {
            return $normalizedData;
        }

        /** @var Event $event */
        $event = $object->getResource();

        if (!$event) {
            return $normalizedData;
        }

        $normalizedData['id'] = $event->getId();

        $location = $object->getLocation();
        if (null !== $location) {
            $normalizedData['locationId'] = $location->getId();

            if (!isset($normalizedData['location']) || !\is_array($normalizedData['location'])) {
                $normalizedData['location'] = [];
            }

            $normalizedData['location']['id'] = $location->getId();
        } else {
            $normalizedData['locationId'] = null;
            $normalizedData['location'] = null;
        }

        $speaker = $object->getSpeaker();
        if (null !== $speaker) {
            $normalizedData['speakerId'] = $speaker->getId();

            if (isset($normalizedData['speaker']) && \is_array($normalizedData['speaker'])) {
                $normalizedData['speaker']['id'] = $speaker->getId();
            }
        }

        $author = $object->getAuthor();
        if (null !== $author) {
            $normalizedData['authorId'] = $author->getId();
        }

        $image = $object->getImage();
        if (null !== $image) {
            if (!isset($normalizedData['image']) || !\is_array($normalizedData['image'])) {
                $normalizedData['image'] = [];
            }
            $normalizedData['image']['id'] = $image->getId();
        }

        $pdf = $object->getPdf();
        if (null !== $pdf) {
            if (!isset($normalizedData['pdf']) || !\is_array($normalizedData['pdf'])) {
                $normalizedData['pdf'] = [];
            }
            $normalizedData['pdf']['id'] = $pdf->getId();
        }

        /*
        $image = $object->getImage();
        if (null !== $image) {
            $normalizedData['image'] = ['id' => $image->getId()];
        } else {
            $normalizedData['image'] = null;
        }

        $pdf = $object->getPdf();
        if (null !== $pdf) {
            $normalizedData['pdf'] = ['id' => $pdf->getId()];
        } else {
            $normalizedData['pdf'] = null;
        }
        */

        return $normalizedData;
    }
}