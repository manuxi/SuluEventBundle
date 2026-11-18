<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Routing;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Routing\EventRouteDefaultsProvider;
use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;
use Sulu\Bundle\AdminBundle\Metadata\MetadataProviderRegistry;
use Sulu\Bundle\HttpCacheBundle\CacheLifetime\CacheLifetimeResolverInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Route\Domain\Model\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The website shows only published events: a route of an event without live content answers 404.
 */
class EventRouteDefaultsProviderTest extends AbstractEventDatabaseTestCase
{
    public function testAnEventThatWasNeverPublishedAnswersNotFound(): void
    {
        $uuid = $this->neverPublishedEvent();

        $aggregator = $this->createMock(ContentAggregatorInterface::class);
        $stages     = [];
        $aggregator->method('aggregate')->willReturnCallback(static function (Event $event, array $attributes) use (&$stages) {
            $stages[] = $attributes['stage'];

            if (DimensionContentInterface::STAGE_LIVE === $attributes['stage']) {
                throw new ContentNotFoundException($event, $attributes);
            }

            // the draft exists and would be found, if anyone asked for it
            return new EventDimensionContent($event);
        });

        $provider = new EventRouteDefaultsProvider(
            $this->entityManager,
            $aggregator,
            new MetadataProviderRegistry($this->createStub(ContainerInterface::class)),
            $this->createStub(CacheLifetimeResolverInterface::class),
        );

        try {
            $provider->getDefaults(new Route(Event::RESOURCE_KEY, $uuid, 'de', '/events/draft'));
            $this->fail('A draft must not be shown on the website.');
        } catch (NotFoundHttpException) {
            $this->assertSame([DimensionContentInterface::STAGE_LIVE], $stages, 'the draft is not even asked for');
        }
    }

    private function neverPublishedEvent(): string
    {
        $event = new Event();
        $draft = new EventDimensionContent($event);
        $draft->setStage(DimensionContentInterface::STAGE_DRAFT);
        $draft->setLocale('de');
        $draft->setTitle('Draft only');
        $event->addDimensionContent($draft);
        $this->entityManager->persist($event);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return $event->getUuid();
    }
}
