<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\RecurrenceGenerator;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'sulu:events:generate-recurring',
    description: 'Generate upcoming occurrences for recurring events'
)]
class GenerateRecurringEventsCommand extends Command
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly RecurrenceGenerator $recurrenceGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentManagerInterface $contentManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'lookahead',
            'l',
            InputOption::VALUE_OPTIONAL,
            'Number of days to generate ahead',
            90
        );

        $this->addOption(
            'locale',
            null,
            InputOption::VALUE_OPTIONAL,
            'Locale to use for content (default: en)',
            'en'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $lookahead = (int) $input->getOption('lookahead');
        $locale = (string) $input->getOption('locale');

        $io->title('Generating Recurring Events');

        // Find all recurring events
        $recurringEvents = $this->eventRepository->findRecurringEvents();

        if (empty($recurringEvents)) {
            $io->info('No recurring events found.');
            return Command::SUCCESS;
        }

        $io->writeln(sprintf('Found %d recurring event(s)', count($recurringEvents)));

        $generated = 0;
        $skipped = 0;
        $errors = 0;

        $rangeStart = new \DateTimeImmutable();
        $rangeEnd = new \DateTimeImmutable("+{$lookahead} days");

        foreach ($recurringEvents as $event) {
            if (!$event->getEventRecurrence()) {
                continue;
            }

            // Get title from dimension content for display
            $dimensionContent = $this->contentManager->resolve($event, [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
            ]);

            $title = $dimensionContent instanceof EventDimensionContent
                ? $dimensionContent->getTitle()
                : 'Event #' . $event->getId();

            $io->writeln(sprintf('Processing: %s (ID: %d)', $title, $event->getId()));

            try {
                // Generate occurrences
                $occurrences = $this->recurrenceGenerator->generateOccurrences(
                    $event->getEventRecurrence(),
                    $rangeStart,
                    $rangeEnd
                );

                foreach ($occurrences as $occurrenceDate) {
                    // Check if occurrence already exists
                    if ($this->occurrenceExists($event, $occurrenceDate)) {
                        $skipped++;
                        continue;
                    }

                    // Create new event for this occurrence
                    $newEvent = $this->createEventOccurrence($event, $occurrenceDate, $locale);
                    $this->entityManager->persist($newEvent);
                    $generated++;
                }

                $this->entityManager->flush();

            } catch (\Exception $e) {
                $io->error(sprintf('Error processing event %d: %s', $event->getId(), $e->getMessage()));
                $errors++;
            }
        }

        $io->success(sprintf(
            'Generated %d new event occurrence(s). Skipped %d existing. Errors: %d',
            $generated,
            $skipped,
            $errors
        ));

        return Command::SUCCESS;
    }

    /**
     * Check if occurrence already exists for this date
     */
    private function occurrenceExists(Event $parentEvent, \DateTimeInterface $date): bool
    {
        return $this->eventRepository->count([
                'recurringParent' => $parentEvent->getId(),
                'startDate' => $date
            ]) > 0;
    }

    /**
     * Create new event occurrence based on parent event
     */
    private function createEventOccurrence(
        Event $parentEvent,
        \DateTimeInterface $occurrenceDate,
        string $locale
    ): Event {
        // Create new event entity
        $newEvent = new Event();

        // Calculate duration from parent event
        $duration = $parentEvent->getStartDate()->diff(
            $parentEvent->getEndDate() ?? $parentEvent->getStartDate()
        );

        // Set dates
        $newEvent->setStartDate(\DateTimeImmutable::createFromInterface($occurrenceDate));
        $endDate = (clone $occurrenceDate)->add($duration);
        $newEvent->setEndDate(\DateTimeImmutable::createFromInterface($endDate));

        // Copy basic properties from parent
        $newEvent->setEnabled($parentEvent->getEnabled());
        $newEvent->setLocation($parentEvent->getLocation());
        $newEvent->setType($parentEvent->getType());

        // Mark as recurring child
        $newEvent->setRecurringParent($parentEvent);

        // Persist event first to get ID
        $this->entityManager->persist($newEvent);
        $this->entityManager->flush();

        // Copy content from parent using ContentManager
        $this->copyEventContent($parentEvent, $newEvent, $locale, $occurrenceDate);

        return $newEvent;
    }

    /**
     * Copy content from parent event to new occurrence
     */
    private function copyEventContent(
        Event $parentEvent,
        Event $newEvent,
        string $locale,
        \DateTimeInterface $occurrenceDate
    ): void {
        // Get parent content
        $parentContent = $this->contentManager->resolve($parentEvent, [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ]);

        if (!$parentContent instanceof EventDimensionContent) {
            return;
        }

        // Create draft content for new event
        $newContent = $this->contentManager->resolve($newEvent, [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ]);

        if (!$newContent instanceof EventDimensionContent) {
            return;
        }

        // Copy content fields
        $newContent->setTitle($parentContent->getTitle());
        $newContent->setSubtitle($parentContent->getSubtitle());
        $newContent->setSummary($parentContent->getSummary());
        $newContent->setDescription($parentContent->getDescription());
        $newContent->setFooter($parentContent->getFooter());

        // Copy media
        $newContent->setImage($parentContent->getImage());
        $newContent->setImages($parentContent->getImages());
        $newContent->setPdf($parentContent->getPdf());

        // Copy speaker
        $newContent->setSpeaker($parentContent->getSpeaker());

        // Copy template and data
        $newContent->setTemplateKey($parentContent->getTemplateKey());
        $newContent->setTemplateData($parentContent->getTemplateData());

        // Generate unique route path
        $baseSlug = $parentContent->getRoute()?->getSlug() ?? '/events/event-' . $newEvent->getId();
        $uniqueSlug = $this->generateUniqueSlug($baseSlug, $occurrenceDate);

        // Set route through ContentManager
        $this->contentManager->applyTransition($newEvent, [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ], WorkflowInterface::WORKFLOW_TRANSITION_CREATE);

        // Persist changes
        $this->contentManager->persist($newEvent, [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ], $newContent->getTemplateData());

        // Publish the occurrence
        $this->contentManager->applyTransition($newEvent, [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ], WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH);

        $this->entityManager->flush();
    }

    /**
     * Generate unique slug for occurrence
     */
    private function generateUniqueSlug(string $baseSlug, \DateTimeInterface $date): string
    {
        // Remove trailing slash
        $baseSlug = rtrim($baseSlug, '/');

        // Append date to make unique
        return $baseSlug . '-' . $date->format('Y-m-d');
    }
}