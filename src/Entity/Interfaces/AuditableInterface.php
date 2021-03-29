<?php

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Sulu\Component\Persistence\Model\TimestampableInterface;
use Sulu\Component\Persistence\Model\UserBlameInterface;

/**
 * Composite interface of TimestampableInterface and UserBlameInterface.
 */
interface AuditableInterface extends TimestampableInterface, AuthoredInterface, UserBlameInterface, AuthorInterface
{

}
