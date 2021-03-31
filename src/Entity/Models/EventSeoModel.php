<?php

namespace Manuxi\SuluEventBundle\Entity\Models;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Manuxi\SuluEventBundle\Entity\EventSeo;
use Manuxi\SuluEventBundle\Entity\Interfaces\EventSeoInterface;
use Manuxi\SuluEventBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluEventBundle\Repository\EventSeoRepository;
use Symfony\Component\HttpFoundation\Request;

class EventSeoModel implements EventSeoInterface
{
    use ArrayPropertyTrait;

    private $eventSeoRepository;

    public function __construct(
        EventSeoRepository $eventSeoRepository
    ) {
        $this->eventSeoRepository = $eventSeoRepository;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateEventSeo(EventSeo $eventSeo, Request $request): EventSeo
    {
        $eventSeo = $this->mapDataToEventSeo($eventSeo, $request->request->all()['ext']['seo']);
        return $this->eventSeoRepository->save($eventSeo);
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
        $noIndex = $this->getProperty($data, 'noIndex');
        if ($noIndex) {
            $eventSeo->setNoIndex($noIndex);
        }
        $noFollow = $this->getProperty($data, 'noFollow');
        if ($noFollow) {
            $eventSeo->setNoFollow($noFollow);
        }
        $hideInSitemap = $this->getProperty($data, 'hideInSitemap');
        if ($hideInSitemap) {
            $eventSeo->setHideInSitemap($hideInSitemap);
        }
        return $eventSeo;
    }
}
