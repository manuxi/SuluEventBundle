<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Manuxi\SuluEventBundle\Entity\Interfaces\AuthorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthorListener
{
    const AUTHOR_PROPERTY_NAME = 'author';

    public function __construct(
        private string $userClass,
        private ?TokenStorageInterface $tokenStorage = null
    ) {}

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $metadata = $event->getClassMetadata();
        $reflection = $metadata->getReflectionClass();

        if (null !== $reflection && $reflection->implementsInterface('Manuxi\SuluEventBundle\Entity\Interfaces\AuthorInterface')) {
            if (!$metadata->hasAssociation(self::AUTHOR_PROPERTY_NAME)) {
                $metadata->mapManyToOne([
                    'fieldName' => self::AUTHOR_PROPERTY_NAME,
                    'targetEntity' => $this->userClass,
                    'joinColumns' => [
                        [
                            'name' => 'author_id',
                            'onDelete' => 'SET NULL',
                            'referencedColumnName' => 'id',
                            'nullable' => true,
                        ],
                    ],
                ]);
            }
        }
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        if (null === $this->tokenStorage) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        // if no token, do nothing
        if (null === $token || $token instanceof NullToken) {
            return;
        }

        $user = $this->getUser($token);

        // if no sulu user, do nothing
        if (!$user instanceof UserInterface) {
            return;
        }

        $contact = $user->getContact();

        $this->handleAuthor($event, $contact, true);
        $this->handleAuthor($event, $contact, false);
    }

    private function handleAuthor(OnFlushEventArgs $event, ContactInterface $contact, bool $insertions): void
    {
        $manager = $event->getObjectManager();
        $unitOfWork = $manager->getUnitOfWork();

        $entities = $insertions ? $unitOfWork->getScheduledEntityInsertions() :
            $unitOfWork->getScheduledEntityUpdates();

        foreach ($entities as $authorEntity) {
            if (!$authorEntity instanceof AuthorInterface) {
                continue;
            }

            $meta = $manager->getClassMetadata(\get_class($authorEntity));

            $changeset = $unitOfWork->getEntityChangeSet($authorEntity);
            $recompute = false;

            if ($insertions
                && (!isset($changeset[self::AUTHOR_PROPERTY_NAME]) || null === $changeset[self::AUTHOR_PROPERTY_NAME][1])
            ) {
                $meta->setFieldValue($authorEntity, self::AUTHOR_PROPERTY_NAME, $contact);
                $recompute = true;
            }

            if (true === $recompute) {
                $unitOfWork->recomputeSingleEntityChangeSet($meta, $authorEntity);
            }
        }
    }

    private function getUser(TokenInterface $token): ?UserInterface
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }

}
