<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\Type;

use Manuxi\SuluEventBundle\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class SingleEventSelection extends SimpleContentType
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
        parent::__construct('single_event_selection');
    }

    public function getContentData(PropertyInterface $property): ?Event
    {
        $id = $property->getValue();

        if (empty($id)) {
            return null;
        }

        return $this->entityManager->getRepository(Event::class)->find($id);
    }

    public function getViewData(PropertyInterface $property): array
    {
        return [
            'id' => $property->getValue(),
        ];
    }
}
