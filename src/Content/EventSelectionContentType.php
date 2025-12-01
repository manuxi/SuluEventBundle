<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Component\Content\ContentType\ContentTypeInterface;
use Sulu\Component\Content\Model\ContentPropertyInterface;

class EventSelectionContentType implements ContentTypeInterface
{
    public function __construct(
        private readonly EventRepository $eventRepository
    ) {
    }

    /**
     * @return Event[]
     */
    public function getContentData(ContentPropertyInterface $property): array
    {
        $ids = $property->getValue();
        $locale = $property->getStructure()->getLanguageCode();

        $eventsList = [];
        foreach ($ids ?: [] as $id) {
            // Achtung: findById gibt evtl. null zurück, das muss abgefangen werden
            $event = $this->eventRepository->findById((int) $id, $locale);

            if ($event && $event->isPublished()) {
                $eventsList[] = $event;
            }
        }

        return $eventsList;
    }

    public function getViewData(ContentPropertyInterface $property): mixed
    {
        return $property->getValue();
    }
}