<?php

namespace Manuxi\SuluEventBundle\Sitemap;

use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class EventSitemapProvider implements SitemapProviderInterface
{
    private $eventRepository;
    private $webspaceManager;
    private $locales = [];

    public function __construct(
        EventRepository $eventRepository,
        WebspaceManagerInterface $webspaceManager
    ) {
        $this->eventRepository = $eventRepository;
        $this->webspaceManager = $webspaceManager;
    }

    public function build($page, $scheme, $host)
    {
        $locale = $this->getLocaleByHost($host);

        $result = [];
        foreach ($this->findEvents(self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE) as $event) {
            $event->setLocale($locale);
            $result[] = new SitemapUrl(
                $scheme . '://' . $host . $event->getRoutePath(),
                $event->getLocale(),
                $event->getLocale(),
                $event->getChanged()
            );
        }

        return $result;
    }

    public function createSitemap($scheme, $host)
    {
        return new Sitemap($this->getAlias(), $this->getMaxPage($scheme, $host));
    }

    public function getAlias()
    {
        return 'events';
    }

    /**
     * @TODO: count method in repo
     */
    public function getMaxPage($scheme, $host)
    {
        return ceil(count($this->findEvents()) / self::PAGE_SIZE);
    }

    private function getLocaleByHost($host) {
        if(!\array_key_exists($host, $this->locales)) {
            $portalInformation = $this->webspaceManager->getPortalInformations();
            foreach ($portalInformation as $hostName => $portal) {
                if($hostName === $host) {
                    $this->locales[$host] = $portal->getLocale();
                }
            }
        }
        return $this->locales[$host];
    }

    private function findEvents($limit = null, $offset = null)
    {
        $criteria = [
            'enabled' => true,
        ];

        return $this->eventRepository->findBy($criteria, [], $limit, $offset);
    }
}
