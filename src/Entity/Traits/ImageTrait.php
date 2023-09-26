<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

trait ImageTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=MediaInterface::class)
     * @Serializer\Exclude()
     */
    private ?MediaInterface $image = null;

    public function getImage(): ?MediaInterface
    {
        return $this->image;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("image")
     */
    public function getImageData(): ?array
    {
        if ($image = $this->getImage()) {
            return [
                'id' => $image->getId(),
            ];
        }

        return null;

    }

    public function setImage(?MediaInterface $image): self
    {
        $this->image = $image;
        return $this;
    }
}
