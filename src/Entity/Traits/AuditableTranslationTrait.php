<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

trait AuditableTranslationTrait
{
    use TimestampableTranslationTrait;
    use UserBlameTranslationTrait;
}
