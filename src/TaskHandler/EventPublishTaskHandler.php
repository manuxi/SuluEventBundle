<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\TaskHandler;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\PublishedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventPublishTaskHandler implements AutomationTaskHandlerInterface
{
    public const TITLE = 'sulu_content.task_handler.publish';

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ContentWorkflowInterface $contentWorkflow,
        private readonly EntityManagerInterface $entityManager,
        private readonly DomainEventCollectorInterface $domainEventCollector,
    ) {
    }

    public function configureOptionsResolver(OptionsResolver $optionsResolver): OptionsResolver
    {
        return $optionsResolver
            ->setRequired(['class', 'id', 'locale'])
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('id', 'string')
            ->setAllowedTypes('locale', 'string');
    }

    public function supports(string $entityClass): bool
    {
        return Event::class === $entityClass;
    }

    public function getConfiguration(): TaskHandlerConfiguration
    {
        return TaskHandlerConfiguration::create(self::TITLE);
    }

    public function handle($workload)
    {
        $event = $this->eventRepository->findByUuid($workload['id']);
        if (null === $event) {
            return;
        }

        $this->contentWorkflow->apply(
            $event,
            ['locale' => $workload['locale']],
            WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
        );
        $this->entityManager->flush();
        $this->domainEventCollector->collect(new PublishedEvent($event, []));
    }
}
