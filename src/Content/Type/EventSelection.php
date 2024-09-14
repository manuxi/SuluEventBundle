<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\Type;

use Manuxi\SuluEventBundle\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class EventSelection extends SimpleContentType
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
        parent::__construct('event_selection', []);
    }

    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();

        if (empty($ids)) {
            return [];
        }

        $events = $this->entityManager->getRepository(Event::class)->findBy(['id' => $ids]);

        $idPositions = \array_flip($ids);
        \usort($events, static function (Event $a, Event $b) use ($idPositions) {
            return $idPositions[$a->getId()] - $idPositions[$b->getId()];
        });

        return $events;
    }

    public function getViewData(PropertyInterface $property): array
    {
        return [
            'ids' => $property->getValue(),
        ];
    }
}
