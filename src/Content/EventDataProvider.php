<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\Orm\BaseDataProvider;

class EventDataProvider extends BaseDataProvider
{
    private int $defaultLimit = 12;

    public function getConfiguration(): ProviderConfigurationInterface
    {
        if (null === $this->configuration) {
            $this->configuration = self::createConfigurationBuilder()
                ->enableLimit()
                ->enablePagination()

                ->enablePresentAs()
                ->enableSorting([

                        ['column' => 'startDate', 'title' => 'sulu_event.start_date'],
                        ['column' => 'endDate', 'title' => 'event.end_date'],
                        ['column' => 'translation.title', 'title' => 'sulu_event.title'],

                    ]
                )
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
     * @param \Countable $queryResult
     * @param int|null $limit
     * @param int $page
     * @param int|null $pageSize
     * @return bool
     */
    private function hasNextPage(\Countable $queryResult, ?int $limit, int $page, ?int $pageSize): bool
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

}
