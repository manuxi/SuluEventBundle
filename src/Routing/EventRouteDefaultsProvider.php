<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Routing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\CacheLifetimeMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TypedFormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\MetadataProviderRegistry;
use Sulu\Bundle\HttpCacheBundle\CacheLifetime\CacheLifetimeRequestStore;
use Sulu\Bundle\HttpCacheBundle\CacheLifetime\CacheLifetimeResolverInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\TemplateInterface;
use Sulu\Route\Application\Routing\Matcher\RouteDefaultsProviderInterface;
use Sulu\Route\Domain\Model\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventRouteDefaultsProvider implements RouteDefaultsProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContentAggregatorInterface $contentAggregator,
        private MetadataProviderRegistry $metadataProviderRegistry,
        private CacheLifetimeResolverInterface $cacheLifetimeResolver,
    ) {
    }

    public function getDefaults(Route $route): array
    {
        $id = $route->getResourceId();
        $locale = $route->getLocale();

        $dimensionContent = $this->loadEntity($id, $locale);

        if (null === $dimensionContent) {
            throw new NotFoundHttpException(\sprintf('No content found for id "%s" and locale "%s"', $id, $locale));
        }

        $contentLocale = $dimensionContent->getLocale();
        if (!$contentLocale) {
            throw new NotFoundHttpException(\sprintf('No content found for id "%s" and locale "%s"', $id, $locale));
        }

        if (!$dimensionContent instanceof TemplateInterface) {
            throw new \RuntimeException(\sprintf(
                'Expected to get "%s" from ContentResolver but "%s" given.',
                TemplateInterface::class,
                $dimensionContent::class
            ));
        }

        $templateKey = $dimensionContent->getTemplateKey();
        if (!$templateKey) {
            throw new NotFoundHttpException(\sprintf('No template found for id "%s" and locale "%s"', $id, $locale));
        }

        $templateMetadata = $this->resolveTemplateMetadata(
            $dimensionContent::getTemplateType(),
            $templateKey,
            $contentLocale
        );

        $attributes = [
            'object' => $dimensionContent,
            'view' => $templateMetadata->getView(),
            '_controller' => $templateMetadata->getController(),
        ];

        $cacheLifetime = $this->getCacheLifetime($templateMetadata);
        if ($cacheLifetime) {
            $attributes[CacheLifetimeRequestStore::ATTRIBUTE_KEY] = $cacheLifetime;
        }

        return $attributes;
    }

    public static function getResourceKey(): string
    {
        return Event::RESOURCE_KEY;
    }

    private function loadEntity(string $uuid, string $locale): ?DimensionContentInterface
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('entity')
            ->from(Event::class, 'entity')
            ->leftJoin('entity.dimensionContents', 'dimensionContent')
            ->addSelect('dimensionContent')
            ->where('entity.uuid = :uuid')
            ->setParameter('uuid', $uuid);

        try {
            /** @var Event $entity */
            $entity = $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException) {
            return null;
        }

        try {
            return $this->contentAggregator->aggregate(
                $entity,
                ['locale' => $locale, 'stage' => DimensionContentInterface::STAGE_LIVE, 'version' => 0]
            );
        } catch (ContentNotFoundException) {
            return null;
        }
    }

    private function resolveTemplateMetadata(string $templateType, string $templateKey, string $locale): object
    {
        $formMetadataProvider = $this->metadataProviderRegistry->getMetadataProvider('form');

        /** @var TypedFormMetadata|null $typedMetadata */
        $typedMetadata = $formMetadataProvider->getMetadata($templateType, $locale, []);

        if (!$typedMetadata instanceof TypedFormMetadata) {
            throw new \RuntimeException(\sprintf('No metadata found for template type "%s"', $templateType));
        }

        $forms = $typedMetadata->getForms();
        $formMetadata = $forms[$templateKey] ?? null;

        if (!$formMetadata) {
            throw new \RuntimeException(\sprintf('No form metadata found for template key "%s"', $templateKey));
        }

        return $formMetadata;
    }

    private function getCacheLifetime(object $templateMetadata): ?int
    {
        if (!\method_exists($templateMetadata, 'getCacheLifetime')) {
            return null;
        }

        /** @var CacheLifetimeMetadata|null $cacheLifetimeMetadata */
        $cacheLifetimeMetadata = $templateMetadata->getCacheLifetime();

        if (!$cacheLifetimeMetadata) {
            return null;
        }

        return $this->cacheLifetimeResolver->resolve(
            $cacheLifetimeMetadata->getType(),
            $cacheLifetimeMetadata->getValue()
        );
    }
}
