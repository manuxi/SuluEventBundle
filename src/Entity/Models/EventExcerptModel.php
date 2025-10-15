<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Models;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Manuxi\SuluEventBundle\Entity\EventExcerpt;
use Manuxi\SuluEventBundle\Entity\Interfaces\EventExcerptModelInterface;
use Manuxi\SuluEventBundle\Repository\EventExcerptRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ArrayPropertyTrait;
use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class EventExcerptModel implements EventExcerptModelInterface
{
    use ArrayPropertyTrait;

    public function __construct(
        private EventExcerptRepository $eventExcerptRepository,
        private CategoryManagerInterface $categoryManager,
        private TagManagerInterface $tagManager,
        private MediaRepositoryInterface $mediaRepository
    ) {}

    /**
     * @param EventExcerpt $eventExcerpt
     * @param Request $request
     * @return EventExcerpt
     * @throws EntityNotFoundException
     */
    public function updateEventExcerpt(EventExcerpt $eventExcerpt, Request $request): EventExcerpt
    {
        $eventExcerpt = $this->mapDataToEventExcerpt($eventExcerpt, $request->request->all()['ext']['excerpt']);
        return $this->eventExcerptRepository->save($eventExcerpt);
    }

    /**
     * @param EventExcerpt $eventExcerpt
     * @param array $data
     * @return EventExcerpt
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
