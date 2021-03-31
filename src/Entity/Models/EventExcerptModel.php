<?php

namespace Manuxi\SuluEventBundle\Entity\Models;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Manuxi\SuluEventBundle\Entity\EventExcerpt;
use Manuxi\SuluEventBundle\Entity\Interfaces\EventExcerptInterface;
use Manuxi\SuluEventBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluEventBundle\Repository\EventExcerptRepository;
use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class EventExcerptModel implements EventExcerptInterface
{
    use ArrayPropertyTrait;

    private $eventExcerptRepository;
    private $categoryManager;
    private $tagManager;
    private $mediaRepository;

    public function __construct(
        EventExcerptRepository $eventExcerptRepository,
        CategoryManagerInterface $categoryManager,
        TagManagerInterface $tagManager,
        MediaRepositoryInterface $mediaRepository
    ) {
        $this->eventExcerptRepository = $eventExcerptRepository;
        $this->categoryManager = $categoryManager;
        $this->tagManager = $tagManager;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateEventExcerpt(EventExcerpt $eventExcerpt, Request $request): EventExcerpt
    {
        $eventExcerpt = $this->mapDataToEventExcerpt($eventExcerpt, $request->request->all()['ext']['excerpt']);
        return $this->eventExcerptRepository->save($eventExcerpt);
    }

    /**
     * @throws EntityNotFoundException
     */
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
            $eventExcerpt->removeCategories();
            $categories = $this->categoryManager->findByIds($categoryIds);
            foreach($categories as $category) {
                $eventExcerpt->addCategory($category);
            }
        }

        $tags = $this->getProperty($data, 'tags');
        if ($tags && is_array($tags)) {
            $eventExcerpt->removeTags();
            foreach($tags as $tagName) {
                $eventExcerpt->addTag($this->tagManager->findOrCreateByName($tagName));
            }
        }

        $iconIds = $this->getPropertyMulti($data, ['icon', 'ids']);
        if ($iconIds && is_array($iconIds)) {
            $eventExcerpt->removeIcons();
            foreach($iconIds as $iconId) {
                $icon = $this->mediaRepository->findMediaById((int)$iconId);
                if (!$icon) {
                    throw new EntityNotFoundException($this->mediaRepository->getClassName(), $iconId);
                }
                $eventExcerpt->addIcon($icon);
            }
        }

        $imageIds = $this->getPropertyMulti($data, ['images', 'ids']);
        if ($imageIds && is_array($imageIds)) {
            $eventExcerpt->removeImages();
            foreach($imageIds as $imageId) {
                $image = $this->mediaRepository->findMediaById((int)$imageId);
                if (!$image) {
                    throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
                }
                $eventExcerpt->addImage($image);
            }
        }

        return $eventExcerpt;
    }
}
