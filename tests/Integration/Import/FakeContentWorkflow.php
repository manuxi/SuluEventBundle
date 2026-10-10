<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Import;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

/**
 * Moves the draft row between the places of the workflow and remembers the transitions.
 */
class FakeContentWorkflow implements ContentWorkflowInterface
{
    /** @var list<array{locale: string, transition: string}> */
    public array $transitions = [];

    public function apply(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes, string $transitionName): DimensionContentInterface
    {
        \assert($contentRichEntity instanceof Event);
        $this->transitions[] = ['locale' => $dimensionAttributes['locale'], 'transition' => $transitionName];

        foreach ($contentRichEntity->getDimensionContents() as $row) {
            if ($row instanceof EventDimensionContent
                && DimensionContentInterface::STAGE_DRAFT === $row->getStage()
                && $dimensionAttributes['locale']         === $row->getLocale()
            ) {
                $row->setWorkflowPlace(match ($transitionName) {
                    WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT => WorkflowInterface::WORKFLOW_PLACE_DRAFT,
                    WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH      => WorkflowInterface::WORKFLOW_PLACE_PUBLISHED,
                    default                                             => $row->getWorkflowPlace(),
                });

                return $row;
            }
        }

        throw new \LogicException('no draft row');
    }
}
