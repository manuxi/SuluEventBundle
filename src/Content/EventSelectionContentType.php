<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class EventSelectionContentType extends SimpleContentType
{
    public function __construct(private readonly EventRepository $eventRepository)
    {
        parent::__construct('event_selection');
    }

    /**
     * @return Event[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();
        $locale = $property->getStructure()->getLanguageCode();

        $datetime = new \DateTime();
        $eventsList = [];
        foreach ($ids ?: [] as $id) {
            /* @var $event Event */
            $event = $this->eventRepository->findById((int) $id, $locale);

            /*if ($event && $event->isPublished()
                && (($event->getEndDate() && $event->getEndDate() >= $datetime) || $event->getStartDate() >= $datetime)) {
                $events[] = $event;
            }*/

            if ($event && $event->isPublished()) {
                $eventsList[] = $event;
            }
        }

        return $eventsList;
    }

    public function getViewData(PropertyInterface $property): mixed
    {
        return $property->getValue();
    }
}
