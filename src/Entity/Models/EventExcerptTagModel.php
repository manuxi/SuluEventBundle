<?php

namespace Manuxi\SuluEventBundle\Entity\Models;

use Manuxi\SuluEventBundle\Entity\EventExcerpt;
use Sulu\Bundle\TagBundle\Tag\TagManagerInterface;
use Sulu\Component\Persistence\RelationTrait;

class EventExcerptTagModel
{
    use RelationTrait;

    private $tagManager;

    public function __construct(TagManagerInterface $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    public function processTags(EventExcerpt $eventExcerpt, $tags)
    {
        $get = function ($tag) {
            return $tag->getId();
        };

        $delete = function ($tag) use ($eventExcerpt) {
            return $eventExcerpt->removeTag($tag);
        };

        $update = function () {
            return true;
        };

        $add = function ($tag) use ($eventExcerpt) {
            return $this->addTag($eventExcerpt, $tag);
        };

        return $this->processSubEntities(
            $eventExcerpt->getTags(),
            $tags,
            $get,
            $add,
            $update,
            $delete
        );

    }

    protected function addTag(EventExcerpt $eventExcerpt, $data)
    {
        $resolvedTag = $this->tagManager->findOrCreateByName($data);
        $eventExcerpt->addTag($resolvedTag);

        return true;
    }
}
