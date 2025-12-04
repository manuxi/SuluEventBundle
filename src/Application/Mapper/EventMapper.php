<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Application\Mapper;

use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class EventMapper implements EventMapperInterface
{
    public function __construct(
        private readonly ContentManagerInterface $contentManager,
    ) {
    }

    public function mapEventData(Event $event, array $data): void
    {
        $locale = $data['locale'] ?? null;
        $stage = $data['stage'] ?? DimensionContentInterface::STAGE_DRAFT;

        $dimensionAttributes = [
            'locale' => $locale,
            'stage' => $stage,
        ];

        $this->contentManager->persist($event, $data, $dimensionAttributes);
    }
}