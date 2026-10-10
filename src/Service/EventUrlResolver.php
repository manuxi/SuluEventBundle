<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

/**
 * Absolute urls of an event page, through the webspace manager of Sulu: the webspace of the event and the locale decide
 * which domain (and path prefix) is used.
 */
class EventUrlResolver
{
    public function __construct(private readonly WebspaceManagerInterface $webspaceManager)
    {
    }

    /**
     * The url of the event page; null if the event has no route or the website has no url for the locale.
     */
    public function findUrl(EventDimensionContent $dimensionContent, string $locale): ?string
    {
        $slug = $dimensionContent->getRoute()?->getSlug();
        if (null === $slug || '' === $slug) {
            return null;
        }

        return $this->webspaceManager->findUrlByResourceLocator($slug, null, $locale, $dimensionContent->getMainWebspace());
    }

    /**
     * The url of the start page of the website the event belongs to (for the host name, e.g. in the UID of an iCal event).
     */
    public function findSiteUrl(EventDimensionContent $dimensionContent, string $locale): ?string
    {
        return $this->webspaceManager->findUrlByResourceLocator('/', null, $locale, $dimensionContent->getMainWebspace());
    }
}
