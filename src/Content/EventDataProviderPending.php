<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\DataProviderResult;

class EventDataProviderPending extends EventDataProvider
{
    public function resolveResourceItems(
        array $filters,
        array $propertyParameter,
        array $options = [],
        $limit = null,
        $page = 1,
        $pageSize = null,
    ): DataProviderResult {
        // Force pending type
        $filters['types'] = ['pending'];

        return parent::resolveResourceItems($filters, $propertyParameter, $options, $limit, $page, $pageSize);
    }
}
