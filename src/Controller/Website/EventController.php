<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use JMS\Serializer\SerializerBuilder;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventController extends AbstractController
{
    private $translator;
    private $eventRepository;
    private $webspaceManager;
    private $templateAttributeResolver;
    private $routeRepository;

    public function __construct(
        RequestStack $requestStack,
        MediaManagerInterface $mediaManager,
        EventRepository $eventRepository,
        WebspaceManagerInterface $webspaceManager,
        TranslatorInterface $translator,
        TemplateAttributeResolverInterface $templateAttributeResolver,
        RouteRepositoryInterface $routeRepository
    ) {
        parent::__construct($requestStack, $mediaManager);

        $this->eventRepository           = $eventRepository;
        $this->webspaceManager           = $webspaceManager;
        $this->translator                = $translator;
        $this->templateAttributeResolver = $templateAttributeResolver;
        $this->routeRepository           = $routeRepository;
    }

    /**
     * @throws \Exception
     */
    public function indexAction(Event $event, string $view = 'pages/event', bool $preview = false, bool $partial = false): Response
    {
        $viewTemplate = $this->getViewTemplate($view, $this->request, $preview);

        $parameters = $this->templateAttributeResolver->resolve([
            'event'   => $event,
            'content' => [
                'title'    => $this->translator->trans('events'),
                'subtitle' => $event->getTitle(),
            ],
            'path'          => $event->getRoutePath(),
            'extension'     => $this->extractExtension($event),
            'localizations' => $this->getLocalizationsArrayForEntity($event),
            'created'       => $event->getCreated(),
        ]);

        return $this->prepareResponse($viewTemplate, $parameters, $preview, $partial);
    }

    /**
     * With the help of this method the corresponding localisations for the
     * current event are found e.g. to be linked in the language switcher.
     * @return array<string, array>
     */
    protected function getLocalizationsArrayForEntity(Event $event): array
    {
        $routes = $this->routeRepository->findAllByEntity(Event::class, (string)$event->getId());

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->webspaceManager->findUrlByResourceLocator(
                $route->getPath(),
                null,
                $route->getLocale()
            );

            $localizations[$route->getLocale()] = ['locale' => $route->getLocale(), 'url' => $url];
        }

        return $localizations;
    }

    private function extractExtension(Event $event): array
    {
        $serializer = SerializerBuilder::create()->build();
        return $serializer->toArray($event->getExt());
    }

    /**
     * @return string[]
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                WebspaceManagerInterface::class,
                RouteRepositoryInterface::class,
                TemplateAttributeResolverInterface::class,
            ]
        );
    }

}
