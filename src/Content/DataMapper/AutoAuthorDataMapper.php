<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\DataMapper;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Content\Application\ContentDataMapper\DataMapper\DataMapperInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AutoAuthorDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly ?Security $security,
    ) {
    }

    public function map(
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent,
        array $data,
    ): void {
        if (!$localizedDimensionContent instanceof EventDimensionContent) {
            return;
        }

        if (\array_key_exists('author', $data) && null !== $data['author']) {
            return;
        }

        if (null !== $localizedDimensionContent->getAuthor()) {
            return;
        }

        $contact = $this->getCurrentUserContact();
        if (null === $contact) {
            return;
        }

        $localizedDimensionContent->setAuthor($contact);

        if (null === $localizedDimensionContent->getAuthored()) {
            $localizedDimensionContent->setAuthored(new \DateTimeImmutable());
        }
    }

    private function getCurrentUserContact(): ?ContactInterface
    {
        if (null === $this->security) {
            return null;
        }

        $user = $this->security->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user->getContact();
    }
}