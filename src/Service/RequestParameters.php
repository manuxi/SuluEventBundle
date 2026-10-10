<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Symfony\Component\HttpFoundation\Request;

/**
 * Reads the filter parameters of the website urls. A list may be given as an array (?categories[]=12&categories[]=15)
 * or as a comma separated value (?categories=12,15, like the smart content filters of Sulu); a malformed parameter is
 * treated like a missing one instead of failing the request.
 */
final class RequestParameters
{
    /**
     * @return string[]
     */
    public static function getList(Request $request, string $name): array
    {
        $raw = $request->query->all()[$name] ?? null;

        $values = [];
        foreach (\is_array($raw) ? $raw : [$raw] as $value) {
            if (!\is_scalar($value)) {
                continue;
            }

            foreach (explode(',', (string) $value) as $part) {
                $part = trim($part);
                if ('' !== $part) {
                    $values[] = $part;
                }
            }
        }

        return $values;
    }

    /**
     * True for ?name=1, true, yes, on; everything else, including a missing parameter, is false.
     */
    public static function getFlag(Request $request, string $name): bool
    {
        $raw = $request->query->all()[$name] ?? null;

        return \is_scalar($raw) && true === filter_var($raw, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE);
    }
}
