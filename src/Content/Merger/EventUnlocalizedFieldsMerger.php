<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\Merger;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Content\Application\ContentMerger\Merger\MergerInterface;

class EventUnlocalizedFieldsMerger implements MergerInterface
{
    public function merge(object $targetObject, object $sourceObject): void
    {
        if (!$targetObject instanceof EventDimensionContent) {
            return;
        }
        if (!$sourceObject instanceof EventDimensionContent) {
            return;
        }

        if (null !== $sourceObject->getLocation()) {
            $targetObject->setLocation($sourceObject->getLocation());
        }

        if (null !== $sourceObject->getType()) {
            $targetObject->setType($sourceObject->getType());
        }

        if (null !== $sourceObject->getStartDate()) {
            $targetObject->setStartDate($sourceObject->getStartDate());
        }

        if (null !== $sourceObject->getEndDate()) {
            $targetObject->setEndDate($sourceObject->getEndDate());
        }

        $targetObject->mergeDetailsFrom($sourceObject);

        if (null !== $sourceObject->getEmail()) {
            $targetObject->setEmail($sourceObject->getEmail());
        }

        if (null !== $sourceObject->getPhoneNumber()) {
            $targetObject->setPhoneNumber($sourceObject->getPhoneNumber());
        }

        if (null !== $sourceObject->getImage()) {
            $targetObject->setImage($sourceObject->getImage());
        }

        // The image is a column owned by EventUnlocalizedDataMapper (the template property is skipped by the template data
        // mapper), so it is not in the template data, which is what the website reads for the property "image" (the cards of
        // a list, an event selection). The merged content gets it, as {id} like the form stores a media.
        $image = $targetObject->getImage();
        if (null !== $image) {
            $templateData          = $targetObject->getTemplateData();
            $templateData['image'] = ['id' => $image->getId()];
            $targetObject->setTemplateData($templateData);
        }

        if (null !== $sourceObject->getPdf()) {
            $targetObject->setPdf($sourceObject->getPdf());
        }

        if (null !== $sourceObject->getSpeaker()) {
            $targetObject->setSpeaker($sourceObject->getSpeaker());
        }

        $showAuthor = $sourceObject->getShowAuthor();
        if (null !== $showAuthor) {
            $targetObject->setShowAuthor($showAuthor);
        }

        $showDate = $sourceObject->getShowDate();
        if (null !== $showDate) {
            $targetObject->setShowDate($showDate);
        }
    }
}