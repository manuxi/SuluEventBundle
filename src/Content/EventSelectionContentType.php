<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use DateTime;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class EventSelectionContentType extends SimpleContentType
{
    public function __construct(private EventRepository $eventRepository)
    {
        parent::__construct('event_selection');
    }

    /**
     * @param PropertyInterface $property
     * @return Event[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();
        $locale = $property->getStructure()->getLanguageCode();

        $datetime = new Datetime();
        $events = [];
        foreach ($ids ?: [] as $id) {
            /* @var $event Event */
            $event = $this->eventRepository->findById((int) $id, $locale);
            if ($event && $event->isEnabled()
                && (($event->getEndDate() && $event->getEndDate() >= $datetime) || $event->getStartDate() >= $datetime) ) {
                $events[] = $event;
            }
        }
        return $events;
    }

    public function getViewData(PropertyInterface $property): mixed
    {
        return $property->getValue();
    }
}
