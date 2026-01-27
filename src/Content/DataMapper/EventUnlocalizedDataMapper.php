<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
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
        $this->mapShowAuthor($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapShowDate($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapSocialSettings($unlocalizedDimensionContent, $data);
        $this->mapRecurrence($unlocalizedDimensionContent, $data);
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
            $image = $this->entityManager->getReference(
                Media::class,
                $imageId
            );
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

    private function mapShowAuthor(
        EventDimensionContent $unlocalizedContent,
        EventDimensionContent $localizedContent,
        array $data
    ): void {
        if (!\array_key_exists('showAuthor', $data)) {
            return;
        }

        $showAuthor = (bool) $data['showAuthor'];
        $unlocalizedContent->setShowAuthor($showAuthor);
        $localizedContent->setShowAuthor($showAuthor);
    }

    private function mapShowDate(
        EventDimensionContent $unlocalizedContent,
        EventDimensionContent $localizedContent,
        array $data
    ): void {
        if (!\array_key_exists('showDate', $data)) {
            return;
        }

        $showDate = (bool) $data['showDate'];
        $unlocalizedContent->setShowDate($showDate);
        $localizedContent->setShowDate($showDate);
    }

    private function mapSocialSettings(
        EventDimensionContent $unlocalizedContent,
        array $data
    ): void {
        $hasSocialData = \array_key_exists('twitterShareText', $data)
            || \array_key_exists('facebookShareText', $data)
            || \array_key_exists('linkedInShareText', $data)
            || \array_key_exists('emailShareSubject', $data)
            || \array_key_exists('emailShareBody', $data);

        if (!$hasSocialData) {
            return;
        }

        $socialSettings = $unlocalizedContent->getSocialSettings();
        if (null === $socialSettings) {
            $socialSettings = new EventSocialSettings($unlocalizedContent);
            $unlocalizedContent->setSocialSettings($socialSettings);
            $this->entityManager->persist($socialSettings);
        }

        if (\array_key_exists('twitterShareText', $data)) {
            $socialSettings->setTwitterShareText(
                \is_string($data['twitterShareText']) ? $data['twitterShareText'] : null
            );
        }

        if (\array_key_exists('facebookShareText', $data)) {
            $socialSettings->setFacebookShareText(
                \is_string($data['facebookShareText']) ? $data['facebookShareText'] : null
            );
        }

        if (\array_key_exists('linkedInShareText', $data)) {
            $socialSettings->setLinkedInShareText(
                \is_string($data['linkedInShareText']) ? $data['linkedInShareText'] : null
            );
        }

        if (\array_key_exists('emailShareSubject', $data)) {
            $socialSettings->setEmailShareSubject(
                \is_string($data['emailShareSubject']) ? $data['emailShareSubject'] : null
            );
        }

        if (\array_key_exists('emailShareBody', $data)) {
            $socialSettings->setEmailShareBody(
                \is_string($data['emailShareBody']) ? $data['emailShareBody'] : null
            );
        }
    }

    private function mapRecurrence(
        EventDimensionContent $unlocalizedContent,
        array $data
    ): void {
        $hasRecurrenceData = \array_key_exists('isRecurring', $data)
            || \array_key_exists('frequency', $data)
            || \array_key_exists('interval', $data)
            || \array_key_exists('byWeekday', $data)
            || \array_key_exists('endType', $data)
            || \array_key_exists('count', $data)
            || \array_key_exists('until', $data);

        if (!$hasRecurrenceData) {
            return;
        }

        $recurrence = $unlocalizedContent->getRecurrence();
        if (null === $recurrence) {
            $recurrence = new EventRecurrence($unlocalizedContent);
            $unlocalizedContent->setRecurrence($recurrence);
            $this->entityManager->persist($recurrence);
        }

        if (\array_key_exists('isRecurring', $data)) {
            $recurrence->setIsRecurring((bool) $data['isRecurring']);
        }

        if (\array_key_exists('frequency', $data)) {
            $recurrence->setFrequency(
                \is_string($data['frequency']) ? $data['frequency'] : null
            );
        }

        if (\array_key_exists('interval', $data)) {
            $interval = (int) ($data['interval'] ?? 1);
            $recurrence->setInterval($interval > 0 ? $interval : 1);
        }

        if (\array_key_exists('byWeekday', $data)) {
            $byWeekday = \is_array($data['byWeekday']) ? $data['byWeekday'] : [];
            $recurrence->setByWeekday($byWeekday);
        }

        if (\array_key_exists('endType', $data)) {
            $endType = \is_string($data['endType']) ? $data['endType'] : 'never';
            $recurrence->setEndType($endType);
        }

        if (\array_key_exists('count', $data)) {
            $count = $data['count'] !== null ? (int) $data['count'] : null;
            $recurrence->setCount($count);
        }

        if (\array_key_exists('until', $data)) {
            $until = null;
            if ($data['until']) {
                try {
                    $until = new \DateTime($data['until']);
                } catch (\Exception) {
                    $until = null;
                }
            }
            $recurrence->setUntil($until);
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