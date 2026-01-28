<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\Normalizer;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use Sulu\Content\Application\ContentNormalizer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventNormalizer implements NormalizerInterface
{
    public function __construct(
        private TranslatorInterface $translator,
        private EventTypeSelect $eventTypeSelect,
    ) {
    }

    public function getIgnoredAttributes(object $object): array
    {
        if (!$object instanceof EventDimensionContent) {
            return [];
        }

        return [
            'event',
            'image',
            'images',
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

        $normalizedData['id'] = $event->getUuid();
        $normalizedData['uuid'] = $event->getUuid();

        // Date formatting
        $dateString = '';
        $startDate = $object->getStartDate();
        $endDate = $object->getEndDate();
        $locale = $object->getLocale();
        $dateFormat = $this->translator->trans('sulu_event.date_format', [], 'admin', $locale);

        if ($startDate) {
            $dateString = $startDate->format($dateFormat);
            if ($endDate) {
                $endStr = $endDate->format($dateFormat);
                if ($dateString !== $endStr) {
                    $dateString .= ' - ' . $endStr;
                }
            }
        }
        $normalizedData['date'] = $dateString;

        // Type
        $type = $object->getType() ?? 'default';
        $normalizedData['typeName'] = $this->eventTypeSelect->getTypeName($type);

        // Location
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

        // Speaker
        $speaker = $object->getSpeaker();
        if (null !== $speaker) {
            $normalizedData['speakerId'] = $speaker->getId();
            if (isset($normalizedData['speaker']) && \is_array($normalizedData['speaker'])) {
                $normalizedData['speaker']['id'] = $speaker->getId();
            }
        }

        // Author
        $author = $object->getAuthor();
        if (null !== $author) {
            $normalizedData['authorId'] = $author->getId();
        }

        // Image
        $image = $object->getImage();
        if (null !== $image) {
            if (!isset($normalizedData['image']) || !\is_array($normalizedData['image'])) {
                $normalizedData['image'] = [];
            }
            $normalizedData['image']['id'] = $image->getId();
        }

        // Images
        $images = $object->getImages();
        if (empty($images)) {
            $normalizedData['images'] = ['ids' => []];
        } elseif (!isset($images['ids'])) {
            $normalizedData['images'] = ['ids' => $images];
        } else {
            $normalizedData['images'] = $images;
        }

        // PDF
        $pdf = $object->getPdf();
        if (null !== $pdf) {
            if (!isset($normalizedData['pdf']) || !\is_array($normalizedData['pdf'])) {
                $normalizedData['pdf'] = [];
            }
            $normalizedData['pdf']['id'] = $pdf->getId();
        }

        $socialSettings = $event->getSocialSettings();
        if (null !== $socialSettings) {
            $normalizedData['enableSharing'] = $socialSettings->isEnableSharing();
            $normalizedData['platforms'] = $socialSettings->getPlatforms();
            $normalizedData['facebookUrl'] = $socialSettings->getFacebookUrl();
            $normalizedData['twitterHandle'] = $socialSettings->getTwitterHandle();
            $normalizedData['instagramUrl'] = $socialSettings->getInstagramUrl();
            $normalizedData['linkedinUrl'] = $socialSettings->getLinkedinUrl();
            $normalizedData['customShareText'] = $socialSettings->getCustomShareText();
            $normalizedData['targetGroups'] = $socialSettings->getTargetGroups();
        }
        unset($normalizedData['socialSettings']);

        $recurrence = $event->getRecurrence();
        if (null !== $recurrence) {
            $normalizedData['isRecurring'] = $recurrence->getIsRecurring();
            $normalizedData['frequency'] = $recurrence->getFrequency();
            $normalizedData['interval'] = $recurrence->getInterval();
            $normalizedData['byWeekday'] = $recurrence->getByWeekday();
            $normalizedData['endType'] = $recurrence->getEndType();
            $normalizedData['count'] = $recurrence->getCount();
            $normalizedData['until'] = $recurrence->getUntil()?->format('Y-m-d');
        }

        unset($normalizedData['recurrence']);

        return $normalizedData;
    }
}