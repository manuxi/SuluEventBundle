<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use Countable;
use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Sulu\Component\Serializer\ArraySerializerInterface;
use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\Orm\BaseDataProvider;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventDataProvider extends BaseDataProvider
{
    private int $defaultLimit = 12;

    private TranslatorInterface $translator;

    public function __construct(
        DataProviderRepositoryInterface $repository,
        ArraySerializerInterface $serializer,
        TranslatorInterface $translator
    )
    {
        parent::__construct($repository, $serializer);
        $this->translator = $translator;
    }

    public function getConfiguration(): ProviderConfigurationInterface
    {
        if (null === $this->configuration) {
            $this->configuration = self::createConfigurationBuilder()
                ->enableLimit()
                ->enablePagination()
                ->enablePresentAs()
                ->enableCategories()
                ->enableTypes($this->getTypes())
                ->enableTags()
                ->enableSorting($this->getSorting())
                ->enableView(EventAdmin::EDIT_FORM_VIEW, ['id' => 'id'])
                ->getConfiguration();
        }

        return parent::getConfiguration();
    }

    /**
     * @param mixed[] $data
     * @return array
     */
    protected function decorateDataItems(array $data): array
    {
        return \array_map(
            static function ($item) {
                return new EventDataItem($item);
            },
            $data
        );
    }

    /**
     * Returns flag "hasNextPage".
     * It combines the limit/query-count with the page and page-size.
     *
     * @noinspection PhpUnusedPrivateMethodInspection
     * @param Countable $queryResult
     * @param int|null $limit
     * @param int $page
     * @param int|null $pageSize
     * @return bool
     */
    private function hasNextPage(Countable $queryResult, ?int $limit, int $page, ?int $pageSize): bool
    {
        $count = $queryResult->count();

        if (null === $pageSize || $pageSize > $this->defaultLimit) {
            $pageSize = $this->defaultLimit;
        }

        $offset = ($page - 1) * $pageSize;
        if ($limit && $offset + $pageSize > $limit) {
            return false;
        }

        return $count > ($page * $pageSize);
    }

    private function getSorting(): array
    {
        return [
            ['column' => 'event.startDate', 'title' => 'sulu_event.sorting.start_date'],
            ['column' => 'event.endDate', 'title' => 'sulu_event.sorting.end_date'],
            ['column' => 'translation.title', 'title' => 'sulu_event.sorting.title'],
        ];
    }

    private function getTypes(): array
    {
        return [
            ['type' => 'pending', 'title' => $this->translator->trans('sulu_event.filter.pending',[],'admin')],
            ['type' => 'expired', 'title' => $this->translator->trans('sulu_event.filter.expired',[],'admin')],
        ];
    }

}
