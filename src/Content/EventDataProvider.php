<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Component\Serializer\ArraySerializerInterface;
use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\DataProviderResult;
use Sulu\Component\SmartContent\Orm\BaseDataProvider;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventDataProvider extends BaseDataProvider
{
    private int $defaultLimit = 12;

    public function __construct(
        DataProviderRepositoryInterface $repository,
        ArraySerializerInterface $serializer,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    ) {
        parent::__construct($repository, $serializer);
    }

    private function getTypes(): array
    {
        return [
            ['type' => 'pending', 'title' => $this->translator->trans('sulu_event.filter.pending', [], 'admin')],
            ['type' => 'expired', 'title' => $this->translator->trans('sulu_event.filter.expired', [], 'admin')],
        ];
    }

    private function getSorting(): array
    {
        return [
            ['column' => 'event.startDate', 'title' => 'sulu_event.sorting.start_date'],
            ['column' => 'event.endDate', 'title' => 'sulu_event.sorting.end_date'],
            ['column' => 'translation.authored', 'title' => 'sulu_event.authored'],
            ['column' => 'translation.title', 'title' => 'sulu_event.title'],
            ['column' => 'translation.published', 'title' => 'sulu_event.published'],
            ['column' => 'translation.publishedAt', 'title' => 'sulu_event.published_at'],
        ];
    }

    public function getConfiguration(): ProviderConfigurationInterface
    {
        if (null === $this->configuration) {
            $this->configuration = self::createConfigurationBuilder()
                ->enableLimit()
                ->enablePagination()
                ->enablePresentAs()
                ->enableCategories()
                ->enableTags()
                ->enableTypes($this->getTypes())
                ->enableSorting($this->getSorting())
                ->enableView(EventAdmin::EDIT_FORM_VIEW, ['id' => 'id'])
                ->getConfiguration();
        }

        return parent::getConfiguration();
    }

    public function resolveResourceItems(
        array $filters,
        array $propertyParameter,
        array $options = [],
        $limit = null,
        $page = 1,
        $pageSize = null,
    ): DataProviderResult {
        $locale = $options['locale'];
        $request = $this->requestStack->getCurrentRequest();
        $options['page'] = $request->get('p');
        $events = $this->entityManager->getRepository(Event::class)->findByFilters($filters, $page, $pageSize, $limit, $locale, $options);

        return new DataProviderResult($events, $this->entityManager->getRepository(Event::class)->hasNextPage($filters, $page, $pageSize, $limit, $locale, $options));
    }

    protected function decorateDataItems(array $data): array
    {
        return \array_map(
            static function ($item) {
                return new EventDataItem($item);
            },
            $data
        );
    }
}
