<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Preview;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\PreviewBundle\Preview\PreviewContext;
use Sulu\Bundle\PreviewBundle\Preview\Provider\PreviewDefaultsProviderInterface;

class EventObjectProvider implements PreviewDefaultsProviderInterface
{
    public function __construct(
        private EventRepository $eventRepository
    ) {
    }

    public function getDefaults(PreviewContext $previewContext): array
    {
        $object = $this->eventRepository->findById(
            (int) $previewContext->getId(),
            $previewContext->getLocale()
        );

        if (!$object) {
            return [];
        }

        return [
            '_controller' => 'Manuxi\SuluEventBundle\Controller\Website\EventController::indexAction',
            'event' => $object,
        ];
    }

    public function updateValues(PreviewContext $previewContext, array $defaults, array $data): array
    {
        /** @var Event $object */
        $object = $defaults['event'];

        // TODO: Implement

        // if (isset($data['title'])) {
        //     $object->setTitle($data['title']);
        // }
        // if (isset($data['description'])) {
        //     $object->setDescription($data['description']);
        // }

        return $defaults;
    }

    public function updateContext(PreviewContext $previewContext, array $defaults, array $context): array
    {
        /** @var Event $object */
        $object = $defaults['event'];

        /*if (\array_key_exists('template', $context)) {
            $object->setStructureType($context['template']);
        }*/

        return $defaults;
    }

    public function getSecurityContext(PreviewContext $previewContext): ?string
    {
        return 'sulu_events.events';
    }
}