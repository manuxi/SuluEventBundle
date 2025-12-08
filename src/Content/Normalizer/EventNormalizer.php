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

        return $normalizedData;
    }
}