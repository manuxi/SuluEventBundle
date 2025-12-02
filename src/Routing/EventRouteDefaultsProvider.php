<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Routing;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\AdminBundle\Metadata\MetadataProviderRegistry;
use Sulu\Bundle\HttpCacheBundle\CacheLifetime\CacheLifetimeResolverInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Infrastructure\Sulu\Route\ContentRouteDefaultsProvider;
use Sulu\Route\Application\Routing\Matcher\RouteDefaultsProviderInterface;
use Sulu\Route\Domain\Model\Route;

class EventRouteDefaultsProvider extends ContentRouteDefaultsProvider implements RouteDefaultsProviderInterface
{
    public function __construct(
        EntityManagerInterface $entityManager,
        ContentAggregatorInterface $contentAggregator,
        MetadataProviderRegistry $metadataProviderRegistry,
        CacheLifetimeResolverInterface $cacheLifetimeResolver,
    ) {
        parent::__construct($entityManager, $contentAggregator, $metadataProviderRegistry, $cacheLifetimeResolver);
    }

    public function supports($entityClass): bool
    {
        return Event::class === $entityClass;
    }
}
