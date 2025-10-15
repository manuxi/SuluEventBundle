<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Sitemap;

use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class EventSitemapProvider implements SitemapProviderInterface
{
    private array $locales = [];

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly WebspaceManagerInterface $webspaceManager,
    ) {
    }

    public function build($page, $scheme, $host): array
    {
        $locale = $this->getLocaleByHost($host);

        $result = [];
        foreach ($this->findEvents($locale, self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE) as $event) {
            $result[] = new SitemapUrl(
                $scheme.'://'.$host.$event->getRoutePath(),
                $event->getLocale(),
                $event->getLocale(),
                $event->getChanged()
            );
        }

        return $result;
    }

    public function createSitemap($scheme, $host): Sitemap
    {
        return new Sitemap($this->getAlias(), $this->getMaxPage($scheme, $host));
    }

    public function getAlias(): string
    {
        return 'events';
    }

    /**
     * @TODO: count method in repo
     */
    public function getMaxPage($scheme, $host): ?float
    {
        $locale = $this->getLocaleByHost($host);

        return ceil($this->eventRepository->countForSitemap($locale) / self::PAGE_SIZE);
    }

    private function getLocaleByHost($host): string
    {
        if (!\array_key_exists($host, $this->locales)) {
            $portalInformation = $this->webspaceManager->getPortalInformations();
            foreach ($portalInformation as $hostName => $portal) {
                if ($hostName === $host || $portal->getHost() === $host) {
                    $this->locales[$host] = $portal->getLocale();
                }
            }
        }

        return $this->locales[$host];
    }

    private function findEvents(string $locale, ?int $limit = null, ?int $offset = null): array
    {
        return $this->eventRepository->findAllForSitemap($locale, $limit, $offset);
    }
}
