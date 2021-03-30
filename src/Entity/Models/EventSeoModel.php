<?php

namespace Manuxi\SuluEventBundle\Entity\Models;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventSeo;
use Manuxi\SuluEventBundle\Entity\Interfaces\EventSeoInterface;
use Manuxi\SuluEventBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluEventBundle\Repository\EventSeoRepository;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
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

    public function createEventSeo(Request $request): EventSeo
    {
        throw new \Exception('Not implemented yet.');
    }

    public function updateEventSeo(EventSeo $eventSeo, Request $request): EventSeo
    {
        $eventSeo = $this->mapDataToEventSeo($eventSeo, $request->request->all()['ext']['seo']);
        return $this->eventSeoRepository->save($eventSeo);
    }

    /**
     * @throws EntityNotFoundException
     */
    private function findEventSeoByIdAndLocale(int $id, Request $request): EventSeo
    {
        $eventSeo = $this->eventSeoRepository->findById($id, (string) $this->getLocaleFromRequest($request));
        if (!$eventSeo) {
            throw new EntityNotFoundException($this->eventSeoRepository->getClassName(), $id);
        }
        return $eventSeo;
    }

    /**
     * @throws EntityNotFoundException
     */
    private function findEventSeoById(int $id): EventSeo
    {
        $eventSeo = $this->eventSeoRepository->find($id);
        if (!$eventSeo) {
            throw new EntityNotFoundException($this->eventSeoRepository->getClassName(), $id);
        }
        return $eventSeo;
    }

    private function getLocaleFromRequest(Request $request)
    {
        return $request->query->get('locale', null);
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
