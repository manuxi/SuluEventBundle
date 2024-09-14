<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Component\SmartContent\ItemInterface;

#[Serializer\ExclusionPolicy("all")]
class EventDataItem implements ItemInterface
{
    public function __construct(
        private Event $entity,
        private ?EntityManagerInterface $entityManager = null
    ) {}

    #[Serializer\VirtualProperty]
    public function getId(): string
    {
        return (string) $this->entity->getId();
    }

    #[Serializer\VirtualProperty]
    public function getTitle(): string
    {
        return (string) $this->entity->getTitle();
    }

    #[Serializer\VirtualProperty]
    public function getImage(): ?int
    {
        $imageId = $this->entity->getImages()['ids'][0];
        $image = $this->entityManager->getRepository(Media::class)->findById($imageId);;

        if (!\array_key_exists('sulu-50x50', $thumbnails = $image->getThumbnails())) {
            return null;
        }

        return $thumbnails['sulu-50x50'];
    }

    public function getResource(): Event
    {
        return $this->entity;
    }
}
