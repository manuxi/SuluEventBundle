<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Routing;

use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Route\Application\Routing\Generator\RouteGeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventRouteGenerator implements RouteGeneratorInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function generate(mixed $entity, array $options): string
    {
        if (!$entity instanceof Event) {
            throw new \InvalidArgumentException('Entity must be instance of Event');
        }

        $schema = $options['route_schema'];

        // Replace translator expressions
        $schema = preg_replace_callback(
            '/\{translator\.trans\(["\']([^"\']+)["\']\)\}/',
            fn ($matches) => $this->translator->trans($matches[1], [], 'messages', $entity->getLocale()),
            $schema
        );

        // Replace object expressions
        $schema = preg_replace_callback(
            '/\{object\.(\w+)\(\)\}/',
            function ($matches) use ($entity) {
                $method = $matches[1];
                if (method_exists($entity, $method)) {
                    return $this->slugify((string) $entity->$method());
                }

                return '';
            },
            $schema
        );

        // Replace {implode("-", object)} pattern (for title-based slugs)
        $schema = preg_replace_callback(
            '/\{implode\(["\']([^"\']+)["\'],\s*object\)\}/',
            fn ($matches) => $this->slugify($entity->getTitle() ?? 'event-'.$entity->getId()),
            $schema
        );

        return $schema;
    }

    public function getOptionsResolver(array $options): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['route_schema']);
        $resolver->setAllowedTypes('route_schema', 'string');

        return $resolver;
    }

    private function slugify(string $text): string
    {
        $slugger = new AsciiSlugger();

        return strtolower($slugger->slug($text)->toString());
    }
}
