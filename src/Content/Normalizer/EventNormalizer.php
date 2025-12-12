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

        return ['event'];
    }

    public function enhance(object $object, array $normalizedData): array
    {
        if (!$object instanceof EventDimensionContent) {
            return $normalizedData;
        }

        /** @var Event $event */
        $event = $object->getResource();

        $normalizedData['id'] = $event->getId();

        $location = $object->getLocation();
        if (null !== $location) {
            $normalizedData['locationId'] = $location->getId();

            if (isset($normalizedData['location']) && \is_array($normalizedData['location'])) {
                $normalizedData['location']['id'] = $location->getId();
            }
        } else {
            $normalizedData['locationId'] = null;
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

        return $normalizedData;
    }
}