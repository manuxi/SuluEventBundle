<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Common;

use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\EventTranslationRepository;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactory;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class DoctrineListRepresentationFactory
{
    private $restHelper;
    private $listBuilderFactory;
    private $fieldDescriptorFactory;
    private $webspaceManager;
    private $eventTranslationRepository;
    private $mediaManager;

    public function __construct(
        RestHelperInterface $restHelper,
        DoctrineListBuilderFactory $listBuilderFactory,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        WebspaceManagerInterface $webspaceManager,
        EventTranslationRepository $eventTranslationRepository,
        MediaManagerInterface $mediaManager
    ) {
        $this->restHelper                   = $restHelper;
        $this->listBuilderFactory           = $listBuilderFactory;
        $this->fieldDescriptorFactory       = $fieldDescriptorFactory;
        $this->webspaceManager              = $webspaceManager;
        $this->eventTranslationRepository   = $eventTranslationRepository;
        $this->mediaManager                 = $mediaManager;
    }

    /**
     * @param mixed[] $filters
     * @param mixed[] $parameters
     */
    public function createDoctrineListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = []
    ): PaginatedRepresentation
    {
        /** @var DoctrineFieldDescriptor[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors($resourceKey);

        $listBuilder = $this->listBuilderFactory->create($fieldDescriptors['id']->getEntityName());
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        foreach ($parameters as $key => $value) {
            $listBuilder->setParameter($key, $value);
        }

        foreach ($filters as $key => $value) {
            $listBuilder->where($fieldDescriptors[$key], $value);
        }

        $list = $listBuilder->execute();

        $list = $this->addGhostLocaleToListElements($list, $parameters['locale'] ?? null);
        $list = $this->addImagesToListElements($list, $parameters['locale'] ?? null);

        return new PaginatedRepresentation(
            $list,
            $resourceKey,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            (int) $listBuilder->count()
        );
    }

    /**
     * @param mixed[]
     */
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

    private function addGhostLocaleToListElements(array $listeElements, ?string $currentLocale) {
        $availableLocales = $locales = $this->webspaceManager->getAllLocales();
        $localesCount = count($availableLocales);
        if (($key = array_search($currentLocale, $locales)) !== false) {
            unset($locales[$key]);
        }

        $ids = array_filter(array_column($listeElements, 'id'));

        foreach($locales as $locale) {
            $missingLocales = $this->eventTranslationRepository->findMissingLocaleByIds($ids, $locale, $localesCount);
            foreach($missingLocales as $missingLocale) {
                foreach ($listeElements as $key => $element) {
                    if ($element['id'] === (int)$missingLocale['event'] && !array_key_exists('ghostLocale', $element)) {
                        $listeElements[$key]['ghostLocale'] = $locale;
//                        $listeElements[$key]['localizationState'] = [
//                            'state' => 'ghost',
//                            'locale' => $locale
//                        ];
                    }
                }
            }
        }

        return $listeElements;
    }
}
