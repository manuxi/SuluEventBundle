<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\ListBuilder;

use Manuxi\SuluEventBundle\Repository\EventTranslationRepository;
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
        private EventTranslationRepository $eventTranslationRepository,
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
    ): PaginatedRepresentation {
        /** @var DoctrineFieldDescriptor[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors($resourceKey);

        $listBuilder = $this->listBuilderFactory->create($fieldDescriptors['id']->getEntityName());
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        if (isset($fieldDescriptors['startDate'])) {
            $listBuilder->addSelectField($fieldDescriptors['startDate']);
        }
        if (isset($fieldDescriptors['endDate'])) {
            $listBuilder->addSelectField($fieldDescriptors['endDate']);
        }

        foreach ($parameters as $key => $value) {
            $listBuilder->setParameter($key, $value);
        }

        foreach ($filters as $key => $value) {
            $listBuilder->where($fieldDescriptors[$key], $value);
        }

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
        $list = $this->addColorsToListElements($list, $locale);
        $list = $this->formatDateTimeElements($list, $locale);

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
            $startDate = $element['startDate'] ?? null;
            $endDate = $element['endDate'] ?? null;

            if (!$startDate instanceof \DateTimeImmutable) {
                continue;
            }

            $startIsFullDay = '00:00:00' === $startDate->format('H:i:s');
            $endIsFullDay = $endDate instanceof \DateTimeImmutable && '00:00:00' === $endDate->format('H:i:s');

            if ($startIsFullDay) {
                $listElements[$key]['startDate'] = $startDate->format($dateFormat);
            } else {
                $listElements[$key]['startDate'] = $startDate->format($dateTimeFormat);
            }

            if ($endDate instanceof \DateTimeImmutable) {
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
            $startDateObj = $element['startDate'] ?? null;
            $endDateObj = $element['endDate'] ?? null;

            if (!$startDateObj instanceof \DateTimeImmutable) {
                continue;
            }

            $startIsFullDay = $startDateObj->format('H:i:s') === '00:00:00';
            $endIsFullDay = $endDateObj instanceof \DateTimeImmutable && $endDateObj->format('H:i:s') === '00:00:00';
            $isSameDay = $endDateObj instanceof \DateTimeImmutable && $startDateObj->format('Y-m-d') === $endDateObj->format('Y-m-d');

            if ($isSameDay && $startIsFullDay && $endIsFullDay) {
                $listElements[$key]['startDate'] = $startDateObj->format($dateFormat);
                $listElements[$key]['endDate'] = $this->translator->trans('sulu_event.all_day', [], 'admin', $locale);
            } elseif ($isSameDay && !$startIsFullDay && !$endIsFullDay && $endDateObj instanceof \DateTimeImmutable) {
                $listElements[$key]['startDate'] = $startDateObj->format($dateFormat);

                $startMinute = (int) $startDateObj->format('i');
                if ($startMinute === 0) {
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
                    //$listElements[$key]['startDate'] = sprintf('%s %s', $startDateObj->format($dateTimeFormat), $hourLabel);
                }

                if ($endDateObj instanceof \DateTimeImmutable) {
                    if ($endIsFullDay) {
                        $listElements[$key]['endDate'] = $endDateObj->format($dateFormat);
                    } else {
                        $listElements[$key]['endDate'] = $endDateObj->format($dateTimeFormat);
                        //$listElements[$key]['endDate'] = sprintf('%s %s', $endDateObj->format($dateTimeFormat), $hourLabel);
                    }
                } else {
                    $listElements[$key]['endDate'] = '';
                }
            }
        }

        return $listElements;
    }
    private function formatWithClockFormatX(array $listElements, ?string $locale): array
    {
        $dateFormat = $this->translator->trans('sulu_event.date_format', [], 'admin', $locale);
        $hourLabel = $this->translator->trans('sulu_event.hour_label', [], 'admin', $locale);

        foreach ($listElements as $key => $element) {
            $startDate = $element['startDate'] ?? null;
            $endDate = $element['endDate'] ?? null;

            if (!$startDate instanceof \DateTimeImmutable) {
                continue;
            }

            $startIsFullDay = '00:00:00' === $startDate->format('H:i:s');
            $endIsFullDay = $endDate instanceof \DateTimeImmutable && '00:00:00' === $endDate->format('H:i:s');
            $isSameDay = $endDate instanceof \DateTimeImmutable && $startDate->format('Y-m-d') === $endDate->format('Y-m-d');

            // WICHTIG: Überschreibe mit String!
            if ($isSameDay && $startIsFullDay && $endIsFullDay) {
                $listElements[$key]['startDate'] = $startDate->format($dateFormat);
                $listElements[$key]['endDate'] = $this->translator->trans('sulu_event.all_day', [], 'admin', $locale);
            } elseif ($isSameDay && !$startIsFullDay && !$endIsFullDay && $endDate instanceof \DateTimeImmutable) {
                $listElements[$key]['startDate'] = $startDate->format($dateFormat);

                $startMinute = (int) $startDate->format('i');
                if (0 === $startMinute) {
                    $startTime = $startDate->format('H');
                    $endTime = $endDate->format('H:i');
                    $listElements[$key]['endDate'] = sprintf('%s-%s %s', $startTime, $endTime, $hourLabel);
                } else {
                    $startTime = $startDate->format('H:i');
                    $endTime = $endDate->format('H:i');
                    $listElements[$key]['endDate'] = sprintf('%s-%s %s', $startTime, $endTime, $hourLabel);
                }
            } elseif (!$endDate) {
                $listElements[$key]['startDate'] = $startDate->format($dateFormat);
                if ($startIsFullDay) {
                    $listElements[$key]['endDate'] = $this->translator->trans('sulu_event.all_day', [], 'admin', $locale);
                } else {
                    $listElements[$key]['endDate'] = sprintf('%s %s', $startDate->format('H:i'), $hourLabel);
                }
            } else {
                $listElements[$key]['startDate'] = $startDate->format($dateFormat);
                $listElements[$key]['endDate'] = $endDate->format($dateFormat);
            }
        }

        return $listElements;
    }

    private function formatWithTimeLabels(array $listElements, ?string $locale): array
    {
        $dateFormat = $this->translator->trans('sulu_event.date_format', [], 'admin', $locale);
        $dateTimeFormat = $this->translator->trans('sulu_event.datetime_format', [], 'admin', $locale);

        foreach ($listElements as $key => $element) {
            $startDateObj = $element['startDate'] ?? null;
            $endDateObj = $element['endDate'] ?? null;

            if (!$startDateObj instanceof \DateTimeImmutable) {
                continue;
            }

            $startIsFullDay = '00:00:00' === $startDateObj->format('H:i:s');
            $endIsFullDay = $endDateObj instanceof \DateTimeImmutable && '00:00:00' === $endDateObj->format('H:i:s');
            $isSameDay = $endDateObj instanceof \DateTimeImmutable && $startDateObj->format('Y-m-d') === $endDateObj->format('Y-m-d');

            if ($isSameDay && !$startIsFullDay && !$endIsFullDay && $endDateObj instanceof \DateTimeImmutable) {
                $listElements[$key]['startDate'] = $startDateObj->format($dateFormat);
                $timeOfDayLabel = $this->getTimeOfDayLabel($startDateObj, $endDateObj, $locale);
                $listElements[$key]['endDate'] = $timeOfDayLabel ?? ($startDateObj->format('H:i').'-'.$endDateObj->format('H:i'));
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

                if ($endDateObj instanceof \DateTimeImmutable) {
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
            if (\array_key_exists('image', $element)
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
            $missingLocales = $this->eventTranslationRepository->findMissingLocaleByIds($ids, $locale, $localesCount);
            foreach ($missingLocales as $missingLocale) {
                foreach ($listeElements as $key => $element) {
                    if ($element['id'] === (int) $missingLocale['event'] && !array_key_exists('ghostLocale', $element)) {
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
            $listeElements[$key]['typeColor'] = $this->eventTypeSelect->getColor($element['type'] ?? 'default');
            $listeElements[$key]['typeName'] = $this->eventTypeSelect->getTypeName($element['type'] ?? 'default');
        }

        return $listeElements;
    }

}
