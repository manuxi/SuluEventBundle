<?php

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

/**
 * Composite interface of TimestampableTranslationInterface and UserBlameTranslationInterface.
 */
interface AuditableTranslationInterface extends TimestampableTranslationInterface, AuthoredTranslationInterface, UserBlameTranslationInterface, AuthorTranslationInterface
{
}
