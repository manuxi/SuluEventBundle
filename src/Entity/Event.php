<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @implements ContentRichEntityInterface<EventDimensionContent>
 */
class Event implements ContentRichEntityInterface
{
    /**
     * @phpstan-use ContentRichEntityTrait<EventDimensionContent>
     */
    use ContentRichEntityTrait;

    public const RESOURCE_KEY = 'events';
    public const FORM_KEY = 'event_detailed';
    public const LIST_KEY = 'events';
    public const LIST_KEY_PUBLISHED = 'events_published';

    public const SECURITY_CONTEXT = 'sulu.events.events';
    public const TEMPLATE_TYPE = 'event';

    protected string $uuid;

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::v7()->toRfc4122();
        $this->initializeDimensionContents();
    }

    public function getId(): string
    {
        return $this->uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function createDimensionContent(): DimensionContentInterface
    {
        return new EventDimensionContent($this);
    }
}
