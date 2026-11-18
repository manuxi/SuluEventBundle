<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

/**
 * The tag that marks an event as a highlight (sulu_event.featured_tag). Without a configured name the feature is off:
 * no event is featured and a filter for highlights matches nothing. The name is compared without regard to case, like
 * the database does it.
 */
class FeaturedTag
{
    private readonly ?string $name;

    public function __construct(?string $name = null)
    {
        $name       = null === $name ? '' : trim($name);
        $this->name = ''   === $name ? null : $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isEnabled(): bool
    {
        return null !== $this->name;
    }

    /**
     * @param iterable<mixed> $tags tag names (strings) or tags (objects with getName())
     */
    public function isFeatured(iterable $tags): bool
    {
        if (null === $this->name) {
            return false;
        }

        foreach ($tags as $tag) {
            $name = \is_object($tag) && method_exists($tag, 'getName') ? $tag->getName() : $tag;
            if (\is_string($name) && 0 === strcasecmp(trim($name), $this->name)) {
                return true;
            }
        }

        return false;
    }
}
