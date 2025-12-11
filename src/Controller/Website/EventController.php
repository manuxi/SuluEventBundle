<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Bundle\PreviewBundle\Preview\Preview;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Twig\Environment;

class EventController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly TemplateAttributeResolverInterface $templateAttributeResolver,
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly RequestStack $requestStack,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public function indexAction(
        Event $event,
        string $view = '@SuluEvent/event',
        bool $preview = false,
        bool $partial = false,
    ): Response {
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request ? $request->getLocale() : 'en';

        // Use ContentAggregator to properly resolve DimensionContent
        // This handles merging unlocalized + localized content correctly
        $stage = $preview ? DimensionContentInterface::STAGE_DRAFT : DimensionContentInterface::STAGE_LIVE;

        /** @var EventDimensionContent|null $content */
        $content = $this->contentAggregator->aggregate(
            $event,
            [
                'locale' => $locale,
                'stage' => $stage,
            ]
        );

        if (!$content || !$content->getTitle()) {
            // Fallback: Try to find directly in collection (for preview with injected content)
            $content = $this->findDimensionContentInCollection($event, $locale, $stage);
        }

        if (!$content) {
            throw new NotAcceptableHttpException(sprintf('No content found for locale "%s".', $locale));
        }

        $parameters = $this->templateAttributeResolver->resolve([
            'event' => $content,
            'localizations' => $this->getLocalizationsArrayForEntity($event),
        ]);

        $viewTemplate = $view . '.html.twig';

        if (!$this->twig->getLoader()->exists($viewTemplate)) {
            throw new NotAcceptableHttpException(\sprintf('Template "%s" does not exist.', $viewTemplate));
        }

        if ($partial) {
            $twigTemplate = $this->twig->load($viewTemplate);
            $content = $twigTemplate->renderBlock('content', $this->twig->mergeGlobals($parameters));
        } elseif ($preview) {
            $parameters['previewParentTemplate'] = $viewTemplate;
            $parameters['previewContentReplacer'] = Preview::CONTENT_REPLACER;
            $content = $this->twig->render('@SuluWebsite/Preview/preview.html.twig', $parameters);
        } else {
            $content = $this->twig->render($viewTemplate, $parameters);
        }

        return new Response($content);
    }

    /**
     * Fallback method to find DimensionContent in the Event's collection.
     * Used when ContentAggregator doesn't return content (e.g., during preview).
     */
    private function findDimensionContentInCollection(Event $event, string $locale, string $stage): ?EventDimensionContent
    {
        foreach ($event->getDimensionContents() as $dimensionContent) {
            if ($dimensionContent->getLocale() === $locale && $dimensionContent->getStage() === $stage) {
                return $dimensionContent;
            }
        }

        // Try draft stage if live not found
        if ($stage === DimensionContentInterface::STAGE_LIVE) {
            foreach ($event->getDimensionContents() as $dimensionContent) {
                if ($dimensionContent->getLocale() === $locale && $dimensionContent->getStage() === DimensionContentInterface::STAGE_DRAFT) {
                    return $dimensionContent;
                }
            }
        }

        return null;
    }

    protected function getLocalizationsArrayForEntity(Event $event): array
    {
        $routes = $this->routeRepository->findBy([
            'resourceKey' => Event::RESOURCE_KEY,
            'resourceId' => (string) $event->getId(),
        ]);

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->webspaceManager->findUrlByResourceLocator(
                $route->getSlug(),
                null,
                $route->getLocale()
            );

            $localizations[$route->getLocale()] = ['locale' => $route->getLocale(), 'url' => $url];
        }

        return $localizations;
    }
}