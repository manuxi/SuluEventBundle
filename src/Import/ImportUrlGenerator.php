<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Builds the url of an imported event from its title and year ("/events/example-conf-2027") and makes it unique: if
 * a route exists already, "-2", "-3", ... is appended.
 */
class ImportUrlGenerator
{
    /** @var array<string, true> urls handed out in this run but not stored yet (dry run) */
    private array $reserved = [];

    public function __construct(
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly string $prefix = '/events',
    ) {
    }

    public function generate(string $title, ?string $year, string $locale): string
    {
        $text = null !== $year && !str_contains($title, $year) ? $title . ' ' . $year : $title;
        $slug = mb_strtolower((new AsciiSlugger($locale))->slug($text)->toString());
        if ('' === $slug) {
            $slug = 'event' . (null !== $year ? '-' . $year : '');
        }

        $base = '/' . trim($this->prefix, '/');
        $base = '/' === $base ? '' : $base;

        $url = $base . '/' . $slug;
        for ($number = 2; $this->isTaken($url, $locale); ++$number) {
            $url = $base . '/' . $slug . '-' . $number;
        }

        $this->reserved[$locale . '|' . $url] = true;

        return $url;
    }

    private function isTaken(string $url, string $locale): bool
    {
        return isset($this->reserved[$locale . '|' . $url])
            || $this->routeRepository->existBy(['locale' => $locale, 'slug' => $url]);
    }
}
