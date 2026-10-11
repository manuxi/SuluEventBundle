<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Http;

/**
 * A page could not be fetched: not allowed by robots.txt, unreachable, an error status or too large.
 */
class FetchException extends \RuntimeException
{
    /**
     * @param int|null $statusCode the HTTP status if the server answered with an error
     */
    public function __construct(string $message = '', private readonly ?int $statusCode = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function isNotFound(): bool
    {
        return 404 === $this->statusCode || 410 === $this->statusCode;
    }
}
