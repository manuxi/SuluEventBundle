<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\AuthoredInterface;

class AuthoredListener
{
    const AUTHORED_PROPERTY_NAME = 'authored';

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $metadata = $event->getClassMetadata();
        $reflection = $metadata->getReflectionClass();

        if (null !== $reflection && $reflection->implementsInterface('Manuxi\SuluSharedToolsBundle\Entity\Interfaces\AuthoredInterface')) {
            if (!$metadata->hasField(self::AUTHORED_PROPERTY_NAME)) {
                $metadata->mapField([
                    'fieldName' => self::AUTHORED_PROPERTY_NAME,
                    'type' => 'datetime',
                    'notnull' => true,
                ]);
            }
        }
    }

    /**
     * Set the timestamps before update.
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(LifecycleEventArgs $event): void
    {
        $this->handleTimestamp($event);
    }

    /**
     * Set the timestamps before creation.
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event): void
    {
        $this->handleTimestamp($event);
    }

    /**
     * Set the timestamps. If created is NULL then set it. Always
     * set the changed field.
     * @param LifecycleEventArgs $event
     */
    private function handleTimestamp(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof AuthoredInterface) {
            return;
        }

        $meta = $event->getObjectManager()->getClassMetadata(\get_class($entity));

        $authored = $meta->getFieldValue($entity, self::AUTHORED_PROPERTY_NAME);
        if (null === $authored) {
            $meta->setFieldValue($entity, self::AUTHORED_PROPERTY_NAME, new \DateTime());
        }
    }
}
