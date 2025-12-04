<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Application\Mapper;

use Manuxi\SuluEventBundle\Entity\Event;

interface EventMapperInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function mapEventData(Event $event, array $data): void;
}