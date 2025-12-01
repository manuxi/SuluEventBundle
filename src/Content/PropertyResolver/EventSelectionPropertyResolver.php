<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\PropertyResolver;

use Manuxi\SuluEventBundle\Content\ResourceLoader\EventResourceLoader;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Content\Application\PropertyResolver\Resolver\PropertyResolverInterface;

class EventSelectionPropertyResolver implements PropertyResolverInterface
{
    public function resolve(mixed $data, string $locale, array $params = []): ContentView
    {
        if (!\is_array($data) || 0 === \count($data)) {
            return ContentView::create([], ['ids' => [], ...$params]);
        }

        // Convert int IDs to strings for ResourceLoader
        $stringIds = array_map('strval', $data);

        return ContentView::createResolvablesWithReferences(
            ids: $stringIds,
            resourceLoaderKey: EventResourceLoader::getKey(),
            resourceKey: Event::RESOURCE_KEY,
            view: ['ids' => $data, ...$params],
            priority: 150
        );
    }

    public static function getType(): string
    {
        return 'event_selection';
    }
}