<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\Merger;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventRecurrence;
use Manuxi\SuluEventBundle\Entity\EventSocialSettings;
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

        if (null !== $sourceObject->getEmail()) {
            $targetObject->setEmail($sourceObject->getEmail());
        }

        if (null !== $sourceObject->getPhoneNumber()) {
            $targetObject->setPhoneNumber($sourceObject->getPhoneNumber());
        }

        if (null !== $sourceObject->getImage()) {
            $targetObject->setImage($sourceObject->getImage());
        }

        if (null !== $sourceObject->getPdf()) {
            $targetObject->setPdf($sourceObject->getPdf());
        }

        if (null !== $sourceObject->getSpeaker()) {
            $targetObject->setSpeaker($sourceObject->getSpeaker());
        }

        if (null !== $sourceObject->getAuthor()) {
            $targetObject->setAuthor($sourceObject->getAuthor());
        }

        if (null !== $sourceObject->getAuthored()) {
            $targetObject->setAuthored($sourceObject->getAuthored());
        }

        if (null !== $sourceObject->getShowDate()) {
            $targetObject->setShowDate($sourceObject->getShowDate());
        }

        if (null !== $sourceObject->getShowAuthor()) {
            $targetObject->setShowAuthor($sourceObject->getShowAuthor());
        }

        $this->mergeSocialSettings($targetObject, $sourceObject);
        $this->mergeRecurrence($targetObject, $sourceObject);
    }

    private function mergeSocialSettings(
        EventDimensionContent $targetObject,
        EventDimensionContent $sourceObject
    ): void {
        $sourceSocialSettings = $sourceObject->getSocialSettings();

        if (null === $sourceSocialSettings) {
            return;
        }

        $targetSocialSettings = $targetObject->getSocialSettings();

        if (null === $targetSocialSettings) {
            $targetSocialSettings = new EventSocialSettings($targetObject);
            $targetObject->setSocialSettings($targetSocialSettings);
        }

        $targetSocialSettings->setTwitterShareText($sourceSocialSettings->getTwitterShareText());
        $targetSocialSettings->setFacebookShareText($sourceSocialSettings->getFacebookShareText());
        $targetSocialSettings->setLinkedInShareText($sourceSocialSettings->getLinkedInShareText());
        $targetSocialSettings->setEmailShareSubject($sourceSocialSettings->getEmailShareSubject());
        $targetSocialSettings->setEmailShareBody($sourceSocialSettings->getEmailShareBody());
    }

    private function mergeRecurrence(
        EventDimensionContent $targetObject,
        EventDimensionContent $sourceObject
    ): void {
        $sourceRecurrence = $sourceObject->getRecurrence();

        if (null === $sourceRecurrence) {
            return;
        }

        $targetRecurrence = $targetObject->getRecurrence();

        if (null === $targetRecurrence) {
            $targetRecurrence = new EventRecurrence($targetObject);
            $targetObject->setRecurrence($targetRecurrence);
        }

        $targetRecurrence->setIsRecurring($sourceRecurrence->getIsRecurring());
        $targetRecurrence->setFrequency($sourceRecurrence->getFrequency());
        $targetRecurrence->setInterval($sourceRecurrence->getInterval());
        $targetRecurrence->setByWeekday($sourceRecurrence->getByWeekday());
        $targetRecurrence->setEndType($sourceRecurrence->getEndType());
        $targetRecurrence->setCount($sourceRecurrence->getCount());
        $targetRecurrence->setUntil($sourceRecurrence->getUntil());
    }
}