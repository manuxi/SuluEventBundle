<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\PreviewBundle\Preview\Preview;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
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
    ) {
    }

    public function indexAction(
        Event $event,
        string $view = '@SuluEvent/event',
        bool $preview = false,
        bool $partial = false,
    ): Response {
        $parameters = $this->templateAttributeResolver->resolve([
            'event' => $event,
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