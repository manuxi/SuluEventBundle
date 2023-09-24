<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Domain\Event\PublishedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class PublishTaskHandler implements AutomationTaskHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        DomainEventCollectorInterface $domainEventCollector
    )
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->domainEventCollector = $domainEventCollector;
    }

    public function handle($workload)
    {
        if (!\is_array($workload)) {
            return;
        }
        $class = $workload['class'];
        $repository = $this->entityManager->getRepository($class);
        $entity = $repository->findById((int)$workload['id'], $workload['locale']);
        if ($entity === null) {
            return;
        }

        $entity->setPublished(true);

        $this->domainEventCollector->collect(
            new PublishedEvent($entity, $workload)
        );

        $repository->save($entity);

    }

    public function configureOptionsResolver(OptionsResolver $optionsResolver): OptionsResolver
    {
        return $optionsResolver->setRequired(['id', 'locale'])
            ->setAllowedTypes('id', 'string')
            ->setAllowedTypes('locale', 'string');
    }

    public function supports(string $entityClass): bool
    {
        return $entityClass === Event::class || \is_subclass_of($entityClass, Event::class);
    }

    public function getConfiguration(): TaskHandlerConfiguration
    {
        return TaskHandlerConfiguration::create($this->translator->trans("sulu_event.publish", [], 'admin'));
    }
}
