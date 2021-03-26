<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Cocur\Slugify\SlugifyInterface;
use JMS\Serializer\SerializerBuilder;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventController extends AbstractController
{
    private $translator;

    private $slugify;

    private $eventRepository;

    private $webspaceManager;

    private $templateAttributeResolver;

    public function __construct(
        RequestStack $requestStack,
        MediaManagerInterface $mediaManager,
        EventRepository $eventRepository,
        WebspaceManagerInterface $webspaceManager,
        SlugifyInterface $slugify,
        TranslatorInterface $translator,
        TemplateAttributeResolverInterface $templateAttributeResolver
    ) {
        parent::__construct($requestStack, $mediaManager);

        $this->eventRepository           = $eventRepository;
        $this->webspaceManager           = $webspaceManager;
        $this->slugify                   = $slugify;
        $this->translator                = $translator;
        $this->templateAttributeResolver = $templateAttributeResolver;
    }

    /**
     * @Route({
     *     "en": "/events/{id}/{slug}",
     *     "de": "/veranstaltungen/{id}/{slug}"
     * }, name="event")
     */
    public function indexAction(int $id, string $slug): Response
    {
        $event      = $this->eventRepository->findById($id, $this->request->getLocale());
        $parameters = $this->templateAttributeResolver->resolve([
            'event'   => $event,
            'content' => [
                'title'    => $this->translator->trans('pages.events'),
                'subtitle' => $event->getTitle(),
                //                'banners' => $this->getBannerMedia(self::BANNER_IDS) //lets unittests fail
            ],
            'path'          => $this->generateUrl('event', ['id' => $id, 'slug' => $slug]),
            'extension'     => $this->extractExtension($event),
            'localizations' => $this->getLocalizationsArrayForEntity($event),
            'created'       => $event->getCreated(),
        ]);

        return $this->render('pages/event.html.twig', $parameters);
    }

    /**
     * @return array<string, array>
     */
    protected function getLocalizationsArrayForEntity(Event $event): array
    {
        $locales = $this->webspaceManager->getAllLocales();

        $localizations = [];
        foreach ($locales as $locale) {
            $event->setLocale($locale);

            //we don't have a translation
            if (null === $event->getTitle()) {
                $url = null;
            } else {
                $urlParams = [
                    'id'      => $event->getId(),
                    'slug'    => $this->slugify->slugify($event->getTitle()),
                    '_locale' => $locale,
                ];
                $routePath = $this->generateUrl('event', $urlParams);
                $url       = $this->webspaceManager->findUrlByResourceLocator(
                    $routePath,
                    null,
                    $locale
                );
            }

            $localizations[$locale] = [
                'locale' => $locale,
                'url'    => $url,
            ];
        }

        //we have to set back the locale of the event, because $event is passed by reference.
        //We don't want to clone here so just reset the $event's locale...
        $event->setLocale($this->request->getLocale());

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
//    public static function getSubscribedServices()
//    {
//        $subscribedServices = parent::getSubscribedServices();
//
    ////        $subscribedServices['sulu_core.webspace.webspace_manager'] = WebspaceManagerInterface::class;
    ////        $subscribedServices['sulu.repository.route'] = RouteRepositoryInterface::class;
//        $subscribedServices['sulu_website.resolver.template_attribute'] = TemplateAttributeResolverInterface::class;
//
//        return $subscribedServices;
//    }

    /**
     * "seo" => array:7 [▼
     *   "title" => ""
     *   "description" => ""
     *   "keywords" => ""
     *   "canonicalUrl" => ""
     *   "noIndex" => false
     *   "noFollow" => false
     *   "hideInSitemap" => false
     * ].
     *
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function getSeo(Event $event): array
    {
        return [$event];
    }

    /**
     * "excerpt" => array:8 [▼
     *   "title" => ""
     *   "more" => ""
     *   "description" => ""
     *   "categories" => []
     *   "tags" => []
     *   "segments" => []
     *   "icon" => []
     *   "images" => []
     * ].
     *
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function getExcerpt(Event $event): array
    {
        return [$event];
    }
}
