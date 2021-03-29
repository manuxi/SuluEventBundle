<?php

namespace Manuxi\SuluEventBundle\EventSubscriber\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Manuxi\SuluEventBundle\Entity\Interfaces\AuthorInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthorSubscriber implements EventSubscriber
{
    const AUTHOR_PROPERTY_NAME = 'author';

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $userClass;

    /**
     * @param string $userClass
     * @param TokenStorageInterface|null $tokenStorage
     */
    public function __construct($userClass, TokenStorageInterface $tokenStorage = null)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userClass = $userClass;
    }

    /**
     * Map creator and changer fields to User objects.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
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
                            'name' => 'idUsersAuthor',
                            'onDelete' => 'SET NULL',
                            'referencedColumnName' => 'id',
                            'nullable' => true,
                        ],
                    ],
                ]);
            }
        }
    }

    public function onFlush(OnFlushEventArgs $event)
    {
        if (null === $this->tokenStorage) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        // if no token, do nothing
        if (null === $token || $token instanceof AnonymousToken) {
            return;
        }

        $user = $this->getUser($token);

        // if no sulu user, do nothing
        if (!$user instanceof UserInterface) {
            return;
        }

        $this->handleAuthor($event, $user, true);
        $this->handleAuthor($event, $user, false);
    }

    private function handleAuthor(OnFlushEventArgs $event, UserInterface $user, bool $insertions)
    {
        $manager = $event->getEntityManager();
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
                $meta->setFieldValue($authorEntity, self::AUTHOR_PROPERTY_NAME, $user);
                $recompute = true;
            }

            if (true === $recompute) {
                $unitOfWork->recomputeSingleEntityChangeSet($meta, $authorEntity);
            }
        }
    }

    /**
     * Return the user from the token.
     *
     * @return UserInterface|null
     */
    private function getUser(TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::onFlush,
        ];
    }
}
