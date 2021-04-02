<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

trait AuditableTranslatableTrait
{
    use TimestampableTranslatableTrait;
    use AuthoredTranslatableTrait;
    use UserBlameTranslatableTrait;
    use AuthorTranslatableTrait;
}
