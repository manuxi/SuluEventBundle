<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;

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
    public const FORM_KEY = 'event_details';
    public const LIST_KEY = 'events';
    public const LIST_KEY_PUBLISHED = 'events_published';

    public const SECURITY_CONTEXT = 'sulu.events.events';
    public const TEMPLATE_TYPE = 'event';

    private ?int $id = null;

    public function __construct()
    {
        $this->initializeDimensionContents();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function createDimensionContent(): DimensionContentInterface
    {
        return new EventDimensionContent($this);
    }
}