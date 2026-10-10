<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Http;

final class FetchResult
{
    public function __construct(
        public readonly string $url,
        public readonly string $body,
        public readonly ?string $contentType = null,
        public readonly bool $fromCache = false,
    ) {
    }
}
