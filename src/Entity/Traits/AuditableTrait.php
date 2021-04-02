<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Sulu\Component\Persistence\Model\TimestampableTrait;
use Sulu\Component\Persistence\Model\UserBlameTrait;

trait AuditableTrait
{
    use UserBlameTrait;
    use AuthorTrait;
    use TimestampableTrait;
    use AuthoredTrait;
}
