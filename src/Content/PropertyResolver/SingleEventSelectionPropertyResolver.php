<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\PropertyResolver;

use Manuxi\SuluEventBundle\Content\ResourceLoader\EventResourceLoader;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Content\Application\PropertyResolver\Resolver\PropertyResolverInterface;

class SingleEventSelectionPropertyResolver implements PropertyResolverInterface
{
    public function resolve(mixed $data, string $locale, array $params = []): ContentView
    {
        if (null === $data || !\is_int($data)) {
            return ContentView::create(null, ['id' => null, ...$params]);
        }

        return ContentView::createResolvableWithReferences(
            id: (string) $data,
            resourceLoaderKey: EventResourceLoader::getKey(),
            resourceKey: Event::RESOURCE_KEY,
            view: ['id' => $data, ...$params],
            priority: 150,
            metadata: ['properties' => $params['properties'] ?? null]
        );
    }

    public static function getType(): string
    {
        return 'single_event_selection';
    }
}
