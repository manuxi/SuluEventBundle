<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

trait ImageTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Sulu\Bundle\MediaBundle\Entity\MediaInterface")
     *
     * @Serializer\Exclude
     */
    private $image;

    public function getImage(): ?MediaInterface
    {
        return $this->image;
    }

    /**
     * @return array<string, mixed>|null
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("image")
     */
    public function getImageData(): ?array
    {
        if (!$this->image) {
            return null;
        }

        return [
            'id' => $this->image->getId(),
        ];
    }

    public function setImage(?MediaInterface $image): self
    {
        $this->image = $image;
        return $this;
    }
}
