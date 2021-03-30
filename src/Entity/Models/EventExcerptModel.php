<?php

namespace Manuxi\SuluEventBundle\Entity\Models;

use Manuxi\SuluEventBundle\Entity\EventExcerpt;
use Manuxi\SuluEventBundle\Entity\Interfaces\EventExcerptInterface;
use Manuxi\SuluEventBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluEventBundle\Repository\EventExcerptRepository;
use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class EventExcerptModel implements EventExcerptInterface
{
    use ArrayPropertyTrait;

    private $eventExcerptRepository;
    private $categoryManager;

    public function __construct(
        EventExcerptRepository $eventExcerptRepository,
        CategoryManagerInterface $categoryManager
    ) {
        $this->eventExcerptRepository = $eventExcerptRepository;
        $this->categoryManager = $categoryManager;
    }

    public function updateEventExcerpt(EventExcerpt $eventExcerpt, Request $request): EventExcerpt
    {
        $eventExcerpt = $this->mapDataToEventExcerpt($eventExcerpt, $request->request->all()['ext']['excerpt']);
        return $this->eventExcerptRepository->save($eventExcerpt);
    }

    private function mapDataToEventExcerpt(EventExcerpt $eventExcerpt, array $data): EventExcerpt
    {
        $locale = $this->getProperty($data, 'locale');
        if ($locale) {
            $eventExcerpt->setLocale($locale);
        }

        $title = $this->getProperty($data, 'title');
        if ($title) {
            $eventExcerpt->setTitle($title);
        }

        $more = $this->getProperty($data, 'more');
        if ($more) {
            $eventExcerpt->setMore($more);
        }

        $description = $this->getProperty($data, 'description');
        if ($description) {
            $eventExcerpt->setDescription($description);
        }

        $categoryIds = $this->getProperty($data, 'categories');
        if ($categoryIds && is_array($categoryIds)) {
            $categories = $this->categoryManager->findByIds($categoryIds);
            foreach($categories as $category) {
                $eventExcerpt->addCategory($category);
            }
        }

        //...

        return $eventExcerpt;
    }
}
