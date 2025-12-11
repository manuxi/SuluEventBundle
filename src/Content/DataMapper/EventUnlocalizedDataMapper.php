<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
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
        $this->mapAuthor($localizedDimensionContent, $data);
    }

    private function mapLocation(
        EventDimensionContent $unlocalizedContent,
        EventDimensionContent $localizedContent,
        array $data,
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
        array $data,
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
        array $data,
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
        array $data,
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

        // Object with 'id' property
        if (\is_array($value) && isset($value['id'])) {
            return (int) $value['id'];
        }

        return null;
    }
}
