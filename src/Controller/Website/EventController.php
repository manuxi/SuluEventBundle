<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Exception;
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
    public function __construct(
        RequestStack $requestStack,
        MediaManagerInterface $mediaManager,
        private EventRepository $eventRepository,
        private WebspaceManagerInterface $webspaceManager,
        private TranslatorInterface $translator,
        private TemplateAttributeResolverInterface $templateAttributeResolver,
        private RouteRepositoryInterface $routeRepository
    ) {
        parent::__construct($requestStack, $mediaManager);
    }

    /**
     * @param Event $event
     * @param string $view
     * @param bool $preview
     * @param bool $partial
     * @return Response
     * @throws Exception
     */
    public function indexAction(Event $event, string $view = '@SuluEvent/event', bool $preview = false, bool $partial = false): Response
    {
        $viewTemplate = $this->getViewTemplate($view, $this->request, $preview);

        $parameters = $this->templateAttributeResolver->resolve([
            'event'   => $event,
            'content' => [
                'title'    => $this->translator->trans('sulu_event.events'),
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
     * @param Event $event
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
    public static function getSubscribedServices(): array
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
