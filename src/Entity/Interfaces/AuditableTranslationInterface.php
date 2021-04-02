<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

/**
 * Composite interface of TimestampableTranslationInterface, AuthoredTranslationInterface,
 * UserBlameTranslationInterface and AuthorTranslationInterface.
 */
interface AuditableTranslationInterface extends TimestampableTranslationInterface, AuthoredTranslationInterface, UserBlameTranslationInterface, AuthorTranslationInterface
{
}
