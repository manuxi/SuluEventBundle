<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventRecurrence;
use Manuxi\SuluEventBundle\Entity\EventSocialSettings;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Content\Application\ContentDataMapper\DataMapper\DataMapperInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class EventUnlocalizedDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function map(
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent,
        array $data,
    ): void {
        if (!$localizedDimensionContent instanceof EventDimensionContent) {
            return;
        }

        if (!$unlocalizedDimensionContent instanceof EventDimensionContent) {
            return;
        }

        $this->mapLocation($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapType($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapDates($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapContactInfo($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapImage($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapAuthor($localizedDimensionContent, $data);
        $this->mapSpeaker($localizedDimensionContent, $data);
        $this->mapPdf($localizedDimensionContent, $data);
        $this->mapShowFlags($unlocalizedDimensionContent, $localizedDimensionContent, $data);

        // Get the Event entity - socialSettings and recurrence are now on Event!
        /** @var Event $event */
        $event = $localizedDimensionContent->getEvent();

        $this->mapSocialSettings($event, $data);
        $this->mapRecurrence($event, $data);
    }

    private function mapShowFlags(
        EventDimensionContent $unlocalizedContent,
        EventDimensionContent $localizedContent,
        array $data,
    ): void {
        if (\array_key_exists('showAuthor', $data)) {
            $showAuthor = (bool) $data['showAuthor'];
            $unlocalizedContent->setShowAuthor($showAuthor);
            $localizedContent->setShowAuthor($showAuthor);
        }

        if (\array_key_exists('showDate', $data)) {
            $showDate = (bool) $data['showDate'];
            $unlocalizedContent->setShowDate($showDate);
            $localizedContent->setShowDate($showDate);
        }
    }

    private function mapSocialSettings(Event $event, array $data): void
    {
        // Check for nested data first, then flat data
        $socialData = null;

        if (\array_key_exists('socialSettings', $data) && \is_array($data['socialSettings'])) {
            $socialData = $data['socialSettings'];
        } elseif (\array_key_exists('enableSharing', $data) || \array_key_exists('platforms', $data)) {
            $socialData = [
                'enableSharing' => $data['enableSharing'] ?? false,
                'platforms' => $data['platforms'] ?? null,
                'facebookUrl' => $data['facebookUrl'] ?? null,
                'twitterHandle' => $data['twitterHandle'] ?? null,
                'instagramUrl' => $data['instagramUrl'] ?? null,
                'linkedinUrl' => $data['linkedinUrl'] ?? null,
                'customShareText' => $data['customShareText'] ?? null,
                'targetGroups' => $data['targetGroups'] ?? null,
            ];
        }

        if (empty($socialData)) {
            return;
        }

        // Check if there's any actual content
        $hasContent = false;
        foreach ($socialData as $value) {
            if (!empty($value)) {
                $hasContent = true;
                break;
            }
        }

        if (!$hasContent) {
            return;
        }

        $socialSettings = $event->getSocialSettings();

        if (!$socialSettings) {
            $socialSettings = new EventSocialSettings($event);
            $event->setSocialSettings($socialSettings);
            $this->entityManager->persist($socialSettings);
        }

        if (\array_key_exists('enableSharing', $socialData)) {
            $socialSettings->setEnableSharing((bool) $socialData['enableSharing']);
        }
        if (\array_key_exists('platforms', $socialData)) {
            $socialSettings->setPlatforms($socialData['platforms']);
        }
        if (\array_key_exists('facebookUrl', $socialData)) {
            $socialSettings->setFacebookUrl($socialData['facebookUrl']);
        }
        if (\array_key_exists('twitterHandle', $socialData)) {
            $socialSettings->setTwitterHandle($socialData['twitterHandle']);
        }
        if (\array_key_exists('instagramUrl', $socialData)) {
            $socialSettings->setInstagramUrl($socialData['instagramUrl']);
        }
        if (\array_key_exists('linkedinUrl', $socialData)) {
            $socialSettings->setLinkedinUrl($socialData['linkedinUrl']);
        }
        if (\array_key_exists('customShareText', $socialData)) {
            $socialSettings->setCustomShareText($socialData['customShareText']);
        }
        if (\array_key_exists('targetGroups', $socialData)) {
            $socialSettings->setTargetGroups($socialData['targetGroups']);
        }
    }

    private function mapRecurrence(Event $event, array $data): void
    {
        // Check for nested data first, then flat data
        $recurrenceData = null;

        if (\array_key_exists('recurrence', $data) && \is_array($data['recurrence'])) {
            $recurrenceData = $data['recurrence'];
        } elseif (\array_key_exists('isRecurring', $data)) {
            $recurrenceData = [
                'isRecurring' => $data['isRecurring'] ?? false,
                'frequency' => $data['frequency'] ?? null,
                'interval' => $data['interval'] ?? 1,
                'byWeekday' => $data['byWeekday'] ?? [],
                'endType' => $data['endType'] ?? 'never',
                'count' => $data['count'] ?? null,
                'until' => $data['until'] ?? null,
            ];
        }

        if (empty($recurrenceData)) {
            return;
        }

        $recurrence = $event->getRecurrence();

        if (!$recurrence) {
            $recurrence = new EventRecurrence($event);
            $event->setRecurrence($recurrence);
            $this->entityManager->persist($recurrence);
        }

        if (\array_key_exists('isRecurring', $recurrenceData)) {
            $recurrence->setIsRecurring((bool) $recurrenceData['isRecurring']);
        }
        if (\array_key_exists('frequency', $recurrenceData)) {
            $recurrence->setFrequency($recurrenceData['frequency']);
        }
        if (\array_key_exists('interval', $recurrenceData)) {
            $recurrence->setInterval((int) ($recurrenceData['interval'] ?? 1));
        }
        if (\array_key_exists('byWeekday', $recurrenceData)) {
            $recurrence->setByWeekday($recurrenceData['byWeekday'] ?? []);
        }
        if (\array_key_exists('endType', $recurrenceData)) {
            $recurrence->setEndType($recurrenceData['endType'] ?? 'never');
        }
        if (\array_key_exists('count', $recurrenceData)) {
            $recurrence->setCount($recurrenceData['count'] ? (int) $recurrenceData['count'] : null);
        }
        if (\array_key_exists('until', $recurrenceData)) {
            $until = $recurrenceData['until'] ? new \DateTime($recurrenceData['until']) : null;
            $recurrence->setUntil($until);
        }
    }

    private function mapImage(
        EventDimensionContent $unlocalizedContent,
        EventDimensionContent $localizedContent,
        array $data,
    ): void {
        if (!\array_key_exists('image', $data)) {
            return;
        }

        $imageId = $data['image'];

        if (\is_array($imageId) && isset($imageId['id'])) {
            $imageId = $imageId['id'];
        }

        $image = null;
        if ($imageId) {
            $image = $this->entityManager->getReference(Media::class, $imageId);
        }

        $unlocalizedContent->setImage($image);
        $localizedContent->setImage($image);
    }

    private function mapSpeaker(EventDimensionContent $localizedContent, array $data): void
    {
        if (!\array_key_exists('speaker', $data)) {
            return;
        }

        $speakerId = $data['speaker'];

        if (\is_array($speakerId) && isset($speakerId['id'])) {
            $speakerId = $speakerId['id'];
        }

        if ($speakerId) {
            $speaker = $this->entityManager->getReference(ContactInterface::class, $speakerId);
            $localizedContent->setSpeaker($speaker);
        } else {
            $localizedContent->setSpeaker(null);
        }
    }

    private function mapPdf(EventDimensionContent $localizedContent, array $data): void
    {
        if (!\array_key_exists('pdf', $data)) {
            return;
        }

        $pdfId = $data['pdf'];

        if (\is_array($pdfId) && isset($pdfId['id'])) {
            $pdfId = $pdfId['id'];
        }

        if ($pdfId) {
            $pdf = $this->entityManager->getReference(Media::class, $pdfId);
            $localizedContent->setPdf($pdf);
        } else {
            $localizedContent->setPdf(null);
        }
    }

    private function mapLocation(
        EventDimensionContent $unlocalizedContent,
        EventDimensionContent $localizedContent,
        array $data
    ): void {
        if (!\array_key_exists('locationId', $data) && !\array_key_exists('location', $data)) {
            return;
        }

        $locationId = $this->extractLocationId($data['locationId'] ?? null);

        if (null === $locationId && isset($data['location'])) {
            $locationId = $this->extractLocationId($data['location']);
        }

        if (null !== $locationId) {
            $location = $this->entityManager->find(Location::class, $locationId);
            if ($location) {
                $unlocalizedContent->setLocation($location);
                $localizedContent->setLocation($location);
            }
        } else {
            $unlocalizedContent->setLocation(null);
            $localizedContent->setLocation(null);
        }
    }

    private function mapType(
        EventDimensionContent $unlocalizedContent,
        EventDimensionContent $localizedContent,
        array $data
    ): void {
        if (!\array_key_exists('type', $data)) {
            return;
        }

        $type = $data['type'];
        $unlocalizedContent->setType($type);
        $localizedContent->setType($type);
    }

    private function mapDates(
        EventDimensionContent $unlocalizedContent,
        EventDimensionContent $localizedContent,
        array $data
    ): void {
        if (\array_key_exists('startDate', $data)) {
            $startDate = $data['startDate'] ? new \DateTimeImmutable($data['startDate']) : null;
            $unlocalizedContent->setStartDate($startDate);
            $localizedContent->setStartDate($startDate);
        }

        if (\array_key_exists('endDate', $data)) {
            $endDate = $data['endDate'] ? new \DateTimeImmutable($data['endDate']) : null;
            $unlocalizedContent->setEndDate($endDate);
            $localizedContent->setEndDate($endDate);
        }
    }

    private function mapContactInfo(
        EventDimensionContent $unlocalizedContent,
        EventDimensionContent $localizedContent,
        array $data
    ): void {
        if (\array_key_exists('email', $data)) {
            $email = $data['email'];
            $unlocalizedContent->setEmail($email);
            $localizedContent->setEmail($email);
        }

        if (\array_key_exists('phoneNumber', $data)) {
            $phoneNumber = $data['phoneNumber'];
            $unlocalizedContent->setPhoneNumber($phoneNumber);
            $localizedContent->setPhoneNumber($phoneNumber);
        }
    }

    private function mapAuthor(EventDimensionContent $localizedContent, array $data): void
    {
        if (\array_key_exists('author', $data)) {
            $authorId = $data['author'];
            if (\is_array($authorId) && isset($authorId['id'])) {
                $authorId = $authorId['id'];
            }
            $author = $authorId ? $this->entityManager->getReference(ContactInterface::class, $authorId) : null;
            $localizedContent->setAuthor($author);
        }

        if (\array_key_exists('authored', $data)) {
            $authored = $data['authored'] ? new \DateTimeImmutable($data['authored']) : new \DateTimeImmutable();
            $localizedContent->setAuthored($authored);
        }
    }

    private function extractLocationId(mixed $value): ?int
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (\is_int($value)) {
            return $value;
        }

        if (\is_string($value) && \is_numeric($value)) {
            return (int) $value;
        }

        if (\is_array($value) && isset($value['id'])) {
            return (int) $value['id'];
        }

        return null;
    }
}