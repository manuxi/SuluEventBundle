<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Models;

use Manuxi\SuluEventBundle\Entity\EventSeo;
use Manuxi\SuluEventBundle\Entity\Interfaces\EventSeoModelInterface;
use Manuxi\SuluEventBundle\Repository\EventSeoRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ArrayPropertyTrait;
use Symfony\Component\HttpFoundation\Request;

class EventSeoModel implements EventSeoModelInterface
{
    use ArrayPropertyTrait;

    public function __construct(
        private EventSeoRepository $eventSeoRepository,
    ) {
    }

    public function updateEventSeo(EventSeo $eventSeo, Request $request): EventSeo
    {
        $data = $request->request->all();
        $seoData = $data['ext']['seo'] ?? null;

        if ($seoData) {
            $eventSeo = $this->mapDataToEventSeo($eventSeo, $seoData);
            $this->eventSeoRepository->save($eventSeo);
        }

        return $eventSeo;
    }

    private function mapDataToEventSeo(EventSeo $eventSeo, array $data): EventSeo
    {
        $locale = $this->getProperty($data, 'locale');
        if ($locale) {
            $eventSeo->setLocale($locale);
        }
        $title = $this->getProperty($data, 'title');
        if ($title) {
            $eventSeo->setTitle($title);
        }
        $description = $this->getProperty($data, 'description');
        if ($description) {
            $eventSeo->setDescription($description);
        }
        $keywords = $this->getProperty($data, 'keywords');
        if ($keywords) {
            $eventSeo->setKeywords($keywords);
        }
        $canonicalUrl = $this->getProperty($data, 'canonicalUrl');
        if ($canonicalUrl) {
            $eventSeo->setCanonicalUrl($canonicalUrl);
        }

        // Booleans - explicit true/false
        if (array_key_exists('noIndex', $data)) {
            $eventSeo->setNoIndex((bool) $data['noIndex']);
        }

        if (array_key_exists('noFollow', $data)) {
            $eventSeo->setNoFollow((bool) $data['noFollow']);
        }

        if (array_key_exists('hideInSitemap', $data)) {
            $eventSeo->setHideInSitemap((bool) $data['hideInSitemap']);
        }

        return $eventSeo;
    }
}
