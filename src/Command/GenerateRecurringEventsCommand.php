<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventTranslation;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\RecurrenceGenerator;
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
        private EventRepository $eventRepository,
        private RecurrenceGenerator $recurrenceGenerator,
        private EntityManagerInterface $entityManager
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $lookahead = (int) $input->getOption('lookahead');
        
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

            $io->writeln(sprintf('Processing: %s (ID: %d)', $event->getTitle(), $event->getId()));

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
                    $newEvent = $this->createEventOccurrence($event, $occurrenceDate);
                    $this->entityManager->persist($newEvent);
                    $generated++;
                }
            } catch (\Exception $e) {
                $io->error(sprintf('Error processing event %d: %s', $event->getId(), $e->getMessage()));
                $errors++;
            }
        }

        $this->entityManager->flush();

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
    private function createEventOccurrence(Event $parentEvent, \DateTimeInterface $occurrenceDate): Event
    {
        $newEvent = new Event();
        
        // Calculate duration from parent event
        $duration = $parentEvent->getStartDate()->diff(
            $parentEvent->getEndDate() ?? $parentEvent->getStartDate()
        );
        
        // Set dates
        $newEvent->setStartDate(\DateTimeImmutable::createFromInterface($occurrenceDate));
        $endDate = (clone $occurrenceDate)->add($duration);
        $newEvent->setEndDate(\DateTimeImmutable::createFromInterface($endDate));
        
        // Copy basic properties
        $newEvent->setEnabled($parentEvent->getEnabled());
        $newEvent->setPublished($parentEvent->getPublished());
        $newEvent->setLocation($parentEvent->getLocation());
        $newEvent->setAuthor($parentEvent->getAuthor());
        
        // Mark as recurring child
        $newEvent->setRecurringParent($parentEvent);
        
        // Copy translations
        foreach ($parentEvent->getTranslations() as $translation) {
            $newTranslation = new EventTranslation();
            $newTranslation->setEvent($newEvent);
            $newTranslation->setLocale($translation->getLocale());
            $newTranslation->setTitle($translation->getTitle());
            $newTranslation->setSubtitle($translation->getSubtitle());
            $newTranslation->setSummary($translation->getSummary());
            $newTranslation->setText($translation->getText());
            $newTranslation->setFooter($translation->getFooter());
            $newTranslation->setRoutePath($this->generateUniqueRoutePath($translation->getRoutePath(), $occurrenceDate));
            
            $newEvent->addTranslation($newTranslation);
        }

        return $newEvent;
    }

    /**
     * Generate unique route path for occurrence
     */
    private function generateUniqueRoutePath(string $basePath, \DateTimeInterface $date): string
    {
        return $basePath . '-' . $date->format('Y-m-d');
    }
}
