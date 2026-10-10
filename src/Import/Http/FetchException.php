<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Http;

/**
 * A page could not be fetched: not allowed by robots.txt, unreachable, an error status or too large.
 */
class FetchException extends \RuntimeException
{
}
