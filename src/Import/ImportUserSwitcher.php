<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Runs the import as the configured Sulu user: creator and changer of the events and of the data they pull in are taken
 * from the security token, and on the command line there is none.
 */
class ImportUserSwitcher
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ImportSettings $settings,
    ) {
    }

    /**
     * @throws \RuntimeException if the configured user does not exist
     */
    public function checkUser(): UserInterface
    {
        $name = $this->settings->user;
        if (null === $name || '' === $name) {
            throw new \RuntimeException('No user is set for the import. Set sulu_event.import.user to the user name of a Sulu user, e.g. a user "event-import".');
        }

        $user = $this->userRepository->findUserByIdentifier($name);
        if (!$user instanceof UserInterface) {
            throw new \RuntimeException(\sprintf('The import user "%s" (sulu_event.import.user) does not exist. Create the user in the Sulu admin or change the setting.', $name));
        }

        return $user;
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function runAsImportUser(callable $callback): mixed
    {
        $user     = $this->checkUser();
        $previous = $this->tokenStorage->getToken();
        $this->tokenStorage->setToken(new UsernamePasswordToken($user, 'admin', $user->getRoles()));

        try {
            return $callback();
        } finally {
            $this->tokenStorage->setToken($previous);
        }
    }
}
