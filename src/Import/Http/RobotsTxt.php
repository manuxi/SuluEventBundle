<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Http;

/**
 * A small parser for robots.txt: the group for the own product token (otherwise "*"), "Allow" and "Disallow" with the
 * wildcards "*" and "$"; the longest matching rule wins, "Allow" wins a tie. An empty "Disallow" allows everything.
 */
final class RobotsTxt
{
    /** @var list<array{type: 'allow'|'disallow', pattern: string}> */
    private array $rules = [];

    public function __construct(string $content, string $userAgent)
    {
        $this->rules = $this->selectRules($content, self::productToken($userAgent));
    }

    /**
     * Everything is allowed (no robots.txt, or an error that means "no rules").
     */
    public static function allowAll(): self
    {
        return new self('', 'x');
    }

    /**
     * Nothing is allowed (the robots.txt could not be read).
     */
    public static function disallowAll(): self
    {
        return new self("User-agent: *\nDisallow: /\n", 'x');
    }

    /**
     * "SuluEventBundle-Import (+https://example.com/contact)" -> "SuluEventBundle-Import".
     */
    public static function productToken(string $userAgent): string
    {
        return 1 === preg_match('/^[^\s\/;(]+/', trim($userAgent), $match) ? $match[0] : 'sulu-event-import';
    }

    /**
     * @param string $pathAndQuery e.g. "/events/ical/?page=2"
     */
    public function isAllowed(string $pathAndQuery): bool
    {
        $pathAndQuery = '' === $pathAndQuery ? '/' : $pathAndQuery;

        $best       = null;
        $bestLength = -1;
        foreach ($this->rules as $rule) {
            if ('' === $rule['pattern']) {
                continue;
            }

            if (!$this->matches($rule['pattern'], $pathAndQuery)) {
                continue;
            }

            $length = mb_strlen($rule['pattern']);
            if ($length > $bestLength || ($length === $bestLength && 'allow' === $rule['type'])) {
                $best       = $rule['type'];
                $bestLength = $length;
            }
        }

        return 'disallow' !== $best;
    }

    /**
     * @return list<array{type: 'allow'|'disallow', pattern: string}>
     */
    private function selectRules(string $content, string $token): array
    {
        /** @var list<array{agents: string[], rules: list<array{type: 'allow'|'disallow', pattern: string}>}> $groups */
        $groups       = [];
        $current      = null;
        $lastWasAgent = false;

        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            $line = trim((string) preg_replace('/#.*$/', '', $line));
            if ('' === $line || !str_contains($line, ':')) {
                continue;
            }

            [$field, $value] = array_map('trim', explode(':', $line, 2));
            $field           = mb_strtolower($field);

            if ('user-agent' === $field) {
                if (!$lastWasAgent || null === $current) {
                    $groups[] = ['agents' => [], 'rules' => []];
                    $current  = array_key_last($groups);
                }
                $groups[$current]['agents'][] = mb_strtolower($value);
                $lastWasAgent                 = true;
                continue;
            }

            $lastWasAgent = false;
            if (null === $current || !\in_array($field, ['allow', 'disallow'], true)) {
                continue;
            }

            $groups[$current]['rules'][] = ['type' => 'allow' === $field ? 'allow' : 'disallow', 'pattern' => $value];
        }

        $token    = mb_strtolower($token);
        $specific = [];
        $general  = [];
        foreach ($groups as $group) {
            foreach ($group['agents'] as $agent) {
                if ('*' === $agent) {
                    $general = [...$general, ...$group['rules']];
                } elseif ('' !== $agent && str_starts_with($token, $agent)) {
                    $specific = [...$specific, ...$group['rules']];
                }
            }
        }

        return [] !== $specific ? $specific : $general;
    }

    private function matches(string $pattern, string $path): bool
    {
        $anchored = str_ends_with($pattern, '$');
        $regex    = preg_quote($anchored ? mb_substr($pattern, 0, -1) : $pattern, '#');
        $regex    = str_replace('\*', '.*', $regex);

        return 1 === preg_match('#^' . $regex . ($anchored ? '$' : '') . '#', $path);
    }
}
