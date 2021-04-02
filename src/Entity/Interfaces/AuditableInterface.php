<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Sulu\Component\Persistence\Model\TimestampableInterface;
use Sulu\Component\Persistence\Model\UserBlameInterface;

/**
 * Composite interface of TimestampableInterface, AuthoredInterface, UserBlameInterface and AuthorInterface.
 */
interface AuditableInterface extends TimestampableInterface, AuthoredInterface, UserBlameInterface, AuthorInterface
{

}
