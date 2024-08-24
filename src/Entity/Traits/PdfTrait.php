<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

trait PdfTrait
{

    #[ORM\ManyToOne(targetEntity: MediaInterface::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Serializer\Exclude]
    private ?MediaInterface $pdf = null;

    public function getPdf(): ?MediaInterface
    {
        return $this->pdf;
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName("pdf")]
    public function getPdfData(): ?array
    {
        if ($pdf = $this->getPdf()) {
            return [
                'id' => $pdf->getId(),
            ];
        }

        return null;

    }

    public function setPdf(?MediaInterface $pdf): self
    {
        $this->pdf = $pdf;
        return $this;
    }
}
