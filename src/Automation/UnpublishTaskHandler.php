<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\UnpublishedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Search\Event\EventPublishedEvent as SearchPublishedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventUnpublishedEvent as SearchUnpublishedEvent;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UnpublishTaskHandler implements AutomationTaskHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle($workload): void
    {
        if (!\is_array($workload)) {
            return;
        }
        $class = $workload['class'];
        $repository = $this->entityManager->getRepository($class);
        $entity = $repository->findById((int) $workload['id'], $workload['locale']);
        if (null === $entity) {
            return;
        }
        $this->dispatcher->dispatch(new SearchUnpublishedEvent($entity));

        $entity->setPublished(false);
        $repository->save($entity);

        $this->domainEventCollector->collect(new UnpublishedEvent($entity, $workload));
        $this->dispatcher->dispatch(new SearchPublishedEvent($entity));

    }

    public function configureOptionsResolver(OptionsResolver $optionsResolver): OptionsResolver
    {
        return $optionsResolver->setRequired(['id', 'locale'])
            ->setAllowedTypes('id', 'string')
            ->setAllowedTypes('locale', 'string');
    }

    public function supports(string $entityClass): bool
    {
        return Event::class === $entityClass || \is_subclass_of($entityClass, Event::class);
    }

    public function getConfiguration(): TaskHandlerConfiguration
    {
        return TaskHandlerConfiguration::create($this->translator->trans('sulu_event.unpublish', [], 'admin'));
    }
}
