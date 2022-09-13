<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Preview;

use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;

class EventObjectProvider implements PreviewObjectProviderInterface
{
    private $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function getObject($id, $locale)
    {
        return $this->eventRepository->findById((int)$id, $locale);
    }

    public function getId($object)
    {
        return $object->getId();
    }

    public function setValues($object, $locale, array $data)
    {
        // TODO: Implement setValues() method.
    }

    public function setContext($object, $locale, array $context)
    {
        if (\array_key_exists('template', $context)) {
            $object->setStructureType($context['template']);
        }

        return $object;
    }

    public function serialize($object)
    {
        return serialize($object);
    }

    public function deserialize($serializedObject, $objectClass)
    {
        return unserialize($serializedObject);
    }
    
    public function getSecurityContext($id, $locale): ?string
    {
        $webspaceKey = $this->documentInspector->getWebspace($this->getObject($id, $locale));

        return PageAdmin::getPageSecurityContext($webspaceKey);
    }
}
