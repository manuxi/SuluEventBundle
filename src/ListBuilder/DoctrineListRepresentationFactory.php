<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\ListBuilder;

use Manuxi\SuluEventBundle\Repository\EventDimensionContentRepository;
use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactory;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\ListRestHelperInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DoctrineListRepresentationFactory
{
    public function __construct(
        private RestHelperInterface $restHelper,
        private ListRestHelperInterface $listRestHelper,
        private DoctrineListBuilderFactory $listBuilderFactory,
        private FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private WebspaceManagerInterface $webspaceManager,
        private EventDimensionContentRepository $eventDimensionContentRepository,
        private MediaManagerInterface $mediaManager,
        private EventTypeSelect $eventTypeSelect,
        private TranslatorInterface $translator,
        private string $listDateFormat,
    ) {
    }

    public function createDoctrineListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        ?string $listKey = null,
    ): PaginatedRepresentation {
        $listKey = $listKey ?? $resourceKey;

        /** @var DoctrineFieldDescriptor[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors($listKey);
        $listBuilder = $this->listBuilderFactory->create($fieldDescriptors['id']->getEntityName());
        $listBuilder->setIdField($fieldDescriptors['id']);
        $listBuilder->distinct(true);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        if (isset($fieldDescriptors['startDate'])) {
            $listBuilder->addSelectField($fieldDescriptors['startDate']);
        }
        if (isset($fieldDescriptors['endDate'])) {
            $listBuilder->addSelectField($fieldDescriptors['endDate']);
        }

        if (isset($fieldDescriptors['image'])) {
            $listBuilder->addSelectField($fieldDescriptors['image']);
        }

        if (isset($fieldDescriptors['publishedState'])) {
            $listBuilder->addSelectField($fieldDescriptors['publishedState']);
        }
        if (isset($fieldDescriptors['published'])) {
            $listBuilder->addSelectField($fieldDescriptors['published']);
        }
        if (isset($fieldDescriptors['livePublished'])) {
            $listBuilder->addSelectField($fieldDescriptors['livePublished']);
        }

        foreach ($parameters as $key => $value) {
            $listBuilder->setParameter($key, $value);
        }

        foreach ($filters as $key => $value) {
            $listBuilder->where($fieldDescriptors[$key], $value);
        }


        if (isset($fieldDescriptors['version'])) {
            $listBuilder->where($fieldDescriptors['version'], 0);
        }

        $listBuilder->addGroupBy($fieldDescriptors['id']);
        $list = $listBuilder->execute();

        // sort the items to reflect the order of the given ids if the list was requested to include specific ids
        $requestedIds = $this->listRestHelper->getIds();
        if (null !== $requestedIds) {
            $idPositions = array_flip($requestedIds);

            usort($list, function ($a, $b) use ($idPositions) {
                return $idPositions[$a['id']] - $idPositions[$b['id']];
            });
        }

        $locale = $parameters['locale'] ?? null;

        $list = $this->addGhostLocaleToListElements($list, $locale);
        $list = $this->addImagesToListElements($list, $locale);
        $list = $this->addColorsToListElements($list);
        $list = $this->addDateToListElements($list, $locale);
        $list = $this->formatDateTimeElements($list, $locale);
        $list = $this->addPublishStateToListElements($list, $listKey);

        return new PaginatedRepresentation(
            $list,
            $resourceKey,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            (int) $listBuilder->count()
        );
    }

    private function formatDateTimeElements(array $listElements, ?string $locale): array
    {
        return match ($this->listDateFormat) {
            'time_labels' => $this->formatWithTimeLabels($listElements, $locale),
            'clock_format' => $this->formatWithClockFormat($listElements, $locale),
            'default' => $this->formatDefault($listElements, $locale),
            default => $this->formatWithClockFormat($listElements, $locale),
        };
    }

    private function formatDefault(array $listElements, ?string $locale): array
    {
        $dateFormat = $this->translator->trans('sulu_event.date_format', [], 'admin', $locale);
        $dateTimeFormat = $this->translator->trans('sulu_event.datetime_format', [], 'admin', $locale);

        foreach ($listElements as $key => $element) {
            $startDate = $this->ensureDateTime($element['startDate'] ?? null);
            $endDate = $this->ensureDateTime($element['endDate'] ?? null);

            if (!$startDate) {
                continue;
            }

            $startIsFullDay = '00:00:00' === $startDate->format('H:i:s');
            $endIsFullDay = $endDate && '00:00:00' === $endDate->format('H:i:s');

            if ($startIsFullDay) {
                $listElements[$key]['startDate'] = $startDate->format($dateFormat);
            } else {
                $listElements[$key]['startDate'] = $startDate->format($dateTimeFormat);
            }

            if ($endDate) {
                if ($endIsFullDay) {
                    $listElements[$key]['endDate'] = $endDate->format($dateFormat);
                } else {
                    $listElements[$key]['endDate'] = $endDate->format($dateTimeFormat);
                }
            } else {
                $listElements[$key]['endDate'] = '';
            }
        }

        return $listElements;
    }

    private function formatWithClockFormat(array $listElements, ?string $locale): array
    {
        $dateFormat = $this->translator->trans('sulu_event.date_format', [], 'admin', $locale);
        $dateTimeFormat = $this->translator->trans('sulu_event.datetime_format', [], 'admin', $locale);
        $hourLabel = $this->translator->trans('sulu_event.hour_label', [], 'admin', $locale);

        foreach ($listElements as $key => $element) {
            $startDateObj = $this->ensureDateTime($element['startDate'] ?? null);
            $endDateObj = $this->ensureDateTime($element['endDate'] ?? null);

            if (!$startDateObj) {
                continue;
            }

            $startIsFullDay = '00:00:00' === $startDateObj->format('H:i:s');
            $endIsFullDay = $endDateObj && '00:00:00' === $endDateObj->format('H:i:s');
            $isSameDay = $endDateObj && $startDateObj->format('Y-m-d') === $endDateObj->format('Y-m-d');

            if ($isSameDay && $startIsFullDay && $endIsFullDay) {
                $listElements[$key]['startDate'] = $startDateObj->format($dateFormat);
                $listElements[$key]['endDate'] = $this->translator->trans('sulu_event.all_day', [], 'admin', $locale);
            } elseif ($isSameDay && !$startIsFullDay && !$endIsFullDay && $endDateObj) {
                $listElements[$key]['startDate'] = $startDateObj->format($dateFormat);

                $startMinute = (int) $startDateObj->format('i');
                if (0 === $startMinute) {
                    $startTime = $startDateObj->format('H');
                    $endTime = $endDateObj->format('H:i');
                    $listElements[$key]['endDate'] = sprintf('%s-%s %s', $startTime, $endTime, $hourLabel);
                } else {
                    $startTime = $startDateObj->format('H:i');
                    $endTime = $endDateObj->format('H:i');
                    $listElements[$key]['endDate'] = sprintf('%s-%s %s', $startTime, $endTime, $hourLabel);
                }
            } elseif (!$endDateObj) {
                $listElements[$key]['startDate'] = $startDateObj->format($dateFormat);
                if ($startIsFullDay) {
                    $listElements[$key]['endDate'] = $this->translator->trans('sulu_event.all_day', [], 'admin', $locale);
                } else {
                    $listElements[$key]['endDate'] = sprintf('%s %s', $startDateObj->format('H:i'), $hourLabel);
                }
            } else {
                if ($startIsFullDay) {
                    $listElements[$key]['startDate'] = $startDateObj->format($dateFormat);
                } else {
                    $listElements[$key]['startDate'] = $startDateObj->format($dateTimeFormat);
                }

                if ($endDateObj) {
                    if ($endIsFullDay) {
                        $listElements[$key]['endDate'] = $endDateObj->format($dateFormat);
                    } else {
                        $listElements[$key]['endDate'] = $endDateObj->format($dateTimeFormat);
                    }
                } else {
                    $listElements[$key]['endDate'] = '';
                }
            }
        }

        return $listElements;
    }

    private function formatWithTimeLabels(array $listElements, ?string $locale): array
    {
        $dateFormat = $this->translator->trans('sulu_event.date_format', [], 'admin', $locale);
        $dateTimeFormat = $this->translator->trans('sulu_event.datetime_format', [], 'admin', $locale);

        foreach ($listElements as $key => $element) {
            $startDateObj = $this->ensureDateTime($element['startDate'] ?? null);
            $endDateObj = $this->ensureDateTime($element['endDate'] ?? null);

            if (!$startDateObj) {
                continue;
            }

            $startIsFullDay = '00:00:00' === $startDateObj->format('H:i:s');
            $endIsFullDay = $endDateObj && '00:00:00' === $endDateObj->format('H:i:s');
            $isSameDay = $endDateObj && $startDateObj->format('Y-m-d') === $endDateObj->format('Y-m-d');

            if ($isSameDay && !$startIsFullDay && !$endIsFullDay && $endDateObj) {
                $listElements[$key]['startDate'] = $startDateObj->format($dateFormat);
                $timeOfDayLabel = $this->getTimeOfDayLabel($startDateObj, $endDateObj, $locale);
                $listElements[$key]['endDate'] = $timeOfDayLabel ?? ($startDateObj->format('H:i') . '-' . $endDateObj->format('H:i'));
            } elseif ($isSameDay && $startIsFullDay && $endIsFullDay) {
                $listElements[$key]['startDate'] = $startDateObj->format($dateFormat);
                $listElements[$key]['endDate'] = $this->translator->trans('sulu_event.all_day', [], 'admin', $locale);
            } elseif (!$endDateObj) {
                $listElements[$key]['startDate'] = $startDateObj->format($dateFormat);
                if ($startIsFullDay) {
                    $listElements[$key]['endDate'] = $this->translator->trans('sulu_event.all_day', [], 'admin', $locale);
                } else {
                    $timeOfDayLabel = $this->getTimeOfDayLabel($startDateObj, null, $locale);
                    $listElements[$key]['endDate'] = $timeOfDayLabel ?? $startDateObj->format('H:i');
                }
            } else {
                if ($startIsFullDay) {
                    $listElements[$key]['startDate'] = $startDateObj->format($dateFormat);
                } else {
                    $listElements[$key]['startDate'] = $startDateObj->format($dateTimeFormat);
                }

                if ($endDateObj) {
                    if ($endIsFullDay) {
                        $listElements[$key]['endDate'] = $endDateObj->format($dateFormat);
                    } else {
                        $listElements[$key]['endDate'] = $endDateObj->format($dateTimeFormat);
                    }
                } else {
                    $listElements[$key]['endDate'] = '';
                }
            }
        }

        return $listElements;
    }

    private function getTimeOfDayLabel(\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, ?string $locale): ?string
    {
        $startHour = (int) $startDate->format('H');

        // No end date - check only start time
        if (!$endDate) {
            if ($startHour >= 6 && $startHour < 12) {
                return $this->translator->trans('sulu_event.morning', [], 'admin', $locale);
            }
            if ($startHour >= 12 && $startHour < 14) {
                return $this->translator->trans('sulu_event.noon', [], 'admin', $locale);
            }
            if ($startHour >= 14 && $startHour < 18) {
                return $this->translator->trans('sulu_event.afternoon', [], 'admin', $locale);
            }
            if ($startHour >= 18 && $startHour < 24) {
                return $this->translator->trans('sulu_event.evening', [], 'admin', $locale);
            }
            if ($startHour >= 0 && $startHour < 6) {
                return $this->translator->trans('sulu_event.night', [], 'admin', $locale);
            }

            return null;
        }

        // With end date - check time range
        $endHour = (int) $endDate->format('H');
        $endMinute = (int) $endDate->format('i');

        // Morning: 6-12
        if ($startHour >= 6 && $endHour < 12) {
            return $this->translator->trans('sulu_event.morning', [], 'admin', $locale);
        }
        if ($startHour >= 6 && 12 === $endHour && 0 === $endMinute) {
            return $this->translator->trans('sulu_event.morning', [], 'admin', $locale);
        }

        // Noon: 12-14
        if ($startHour >= 12 && $endHour < 14) {
            return $this->translator->trans('sulu_event.noon', [], 'admin', $locale);
        }
        if ($startHour >= 12 && 14 === $endHour && 0 === $endMinute) {
            return $this->translator->trans('sulu_event.noon', [], 'admin', $locale);
        }

        // Afternoon: 14-18
        if ($startHour >= 14 && $endHour < 18) {
            return $this->translator->trans('sulu_event.afternoon', [], 'admin', $locale);
        }
        if ($startHour >= 14 && 18 === $endHour && 0 === $endMinute) {
            return $this->translator->trans('sulu_event.afternoon', [], 'admin', $locale);
        }

        // Evening: 18-24
        if ($startHour >= 18 && $endHour < 24) {
            return $this->translator->trans('sulu_event.evening', [], 'admin', $locale);
        }

        // Night: 0-6
        if ($startHour >= 0 && $endHour < 6) {
            return $this->translator->trans('sulu_event.night', [], 'admin', $locale);
        }
        if ($startHour >= 0 && 6 === $endHour && 0 === $endMinute) {
            return $this->translator->trans('sulu_event.night', [], 'admin', $locale);
        }

        return null;
    }

    private function addImagesToListElements(array $listeElements, ?string $locale): array
    {
        $ids = array_filter(array_column($listeElements, 'image'));
        $images = $this->mediaManager->getFormatUrls($ids, $locale);
        foreach ($listeElements as $key => $element) {
            if (
                \array_key_exists('image', $element)
                && $element['image']
                && \array_key_exists($element['image'], $images)
            ) {
                $listeElements[$key]['image'] = $images[$element['image']];
            }
        }

        return $listeElements;
    }

    private function addGhostLocaleToListElements(array $listeElements, ?string $currentLocale)
    {
        $availableLocales = $locales = $this->webspaceManager->getAllLocales();
        $localesCount = count($availableLocales);
        if (($key = array_search($currentLocale, $locales)) !== false) {
            unset($locales[$key]);
        }

        $ids = array_filter(array_column($listeElements, 'id'));

        foreach ($locales as $locale) {
            $missingLocales = $this->eventDimensionContentRepository->findMissingLocaleByIds($ids, $locale, $localesCount);
            foreach ($missingLocales as $missingLocale) {
                foreach ($listeElements as $key => $element) {
                    if ($element['id'] === $missingLocale['event'] && !array_key_exists('ghostLocale', $element)) {
                        $listeElements[$key]['ghostLocale'] = $locale;
                        /*
                        $listeElements[$key]['localizationState'] = [
                            'state' => 'ghost',
                            'locale' => $locale
                        ];
                        */
                    }
                }
            }
        }

        return $listeElements;
    }

    /**
     * Adds types for EventTypeColorFieldTransformer
     * Modify your list/events.xml like described in the readme.
     *
     * @return array
     */
    private function addColorsToListElements(array $listeElements)
    {
        foreach ($listeElements as $key => $element) {
            $type = $element['type'] ?? 'default';
            $listeElements[$key]['typeColor'] = $this->eventTypeSelect->getColor($type);
            $typeName = $this->eventTypeSelect->getTypeName($type);
            $listeElements[$key]['typeName'] = $typeName;
            // Overwrite 'type' with the translated name for display
            $listeElements[$key]['type'] = $typeName;
        }

        return $listeElements;
    }

    private function addPublishStateToListElements(array $listElements, ?string $listKey = null): array
    {
        foreach ($listElements as $key => $element) {
            if ('events_published' === $listKey) {
                $listElements[$key]['publishedState'] = true;
                continue;
            }

            if (empty($element['published']) && !empty($element['livePublished'])) {
                $listElements[$key]['published'] = $element['livePublished'];
            }

            $workflowPlace = $element['publishedState'] ?? $element['workflowPlace'] ?? null;
            $listElements[$key]['publishedState'] = 'published' === $workflowPlace;
        }

        return $listElements;
    }

    /**
     * Helper to ensure we have a DateTimeImmutable object or null.
     */
    private function ensureDateTime(string|\DateTimeInterface|null $date): ?\DateTimeImmutable
    {
        if (null === $date) {
            return null;
        }

        if ($date instanceof \DateTimeImmutable) {
            return $date;
        }

        if ($date instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($date);
        }

        if (is_string($date)) {
            try {
                return new \DateTimeImmutable($date);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    private function addDateToListElements(array $listElements, ?string $locale): array
    {
        $dateFormat = $this->translator->trans('sulu_event.date_format', [], 'admin', $locale);

        foreach ($listElements as $key => $element) {
            $startDate = $this->ensureDateTime($element['startDate'] ?? null);
            $endDate = $this->ensureDateTime($element['endDate'] ?? null);

            if (!$startDate) {
                continue;
            }

            $dateString = $startDate->format($dateFormat);
            if ($endDate) {
                $endStr = $endDate->format($dateFormat);
                if ($dateString !== $endStr) {
                    $dateString .= ' - ' . $endStr;
                }
            }

            $listElements[$key]['date'] = $dateString;
        }

        return $listElements;
    }
}
