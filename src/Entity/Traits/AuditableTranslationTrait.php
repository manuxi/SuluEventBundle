<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

trait AuditableTranslationTrait
{
    use TimestampableTranslationTrait;
    use AuthoredTranslationTrait;
    use UserBlameTranslationTrait;
    use AuthorTranslationTrait;
}
