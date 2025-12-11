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

        // Find all recurring events (now requires locale)
        $recurringEvents = $this->eventRepository->findRecurringEvents($locale);

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
            // Get merged dimension content (includes all fields)
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentManager->resolve($event, [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
            ]);

            if (!$dimensionContent instanceof EventDimensionContent) {
                $io->warning(sprintf('Could not resolve dimension content for event #%d', $event->getId()));
                $errors++;
                continue;
            }

            $recurrence = $dimensionContent->getRecurrence();
            if (!$recurrence || !$recurrence->getIsRecurring()) {
                continue;
            }

            $title = $dimensionContent->getTitle() ?? 'Event #' . $event->getId();

            $io->writeln(sprintf('Processing: %s (ID: %d)', $title, $event->getId()));

            try {
                // Generate occurrences
                $occurrences = $this->recurrenceGenerator->generateOccurrences(
                    $recurrence,
                    $dimensionContent,
                    $rangeStart,
                    $rangeEnd
                );

                foreach ($occurrences as $occurrenceDate) {
                    // Check if occurrence already exists
                    if ($this->occurrenceExists($event, $occurrenceDate, $locale)) {
                        $skipped++;
                        continue;
                    }

                    // Create new event for this occurrence
                    $newEvent = $this->createEventOccurrence(
                        $event,
                        $dimensionContent,
                        $occurrenceDate,
                        $locale
                    );
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
     * Check if occurrence already exists for this date.
     */
    private function occurrenceExists(Event $parentEvent, \DateTimeInterface $date, string $locale): bool
    {
        $events = $this->eventRepository->findByFilters(['locale' => $locale]);

        foreach ($events as $event) {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentManager->resolve($event, [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ]);

            if (!$dimensionContent instanceof EventDimensionContent) {
                continue;
            }

            $startDate = $dimensionContent->getStartDate();
            if ($startDate && $startDate->format('Y-m-d') === $date->format('Y-m-d')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create new event occurrence based on parent event.
     */
    private function createEventOccurrence(
        Event $parentEvent,
        EventDimensionContent $parentDimensionContent,
        \DateTimeInterface $occurrenceDate,
        string $locale
    ): Event {
        // Create new event entity
        $newEvent = new Event();
        $this->entityManager->persist($newEvent);
        $this->entityManager->flush();

        // Calculate duration from parent event
        $parentStartDate = $parentDimensionContent->getStartDate();
        $parentEndDate = $parentDimensionContent->getEndDate();

        $duration = null;
        if ($parentStartDate && $parentEndDate) {
            $duration = $parentStartDate->diff($parentEndDate);
        }

        // Calculate new end date
        $newStartDate = \DateTimeImmutable::createFromInterface($occurrenceDate);
        $newEndDate = $duration ? $newStartDate->add($duration) : null;

        // Build data array from parent (all fields from merged dimensionContent)
        $data = [
            'title' => $parentDimensionContent->getTitle(),
            'subtitle' => $parentDimensionContent->getSubtitle(),
            'summary' => $parentDimensionContent->getSummary(),
            'text' => $parentDimensionContent->getText(),
            'footer' => $parentDimensionContent->getFooter(),
            'type' => $parentDimensionContent->getType(),
            'startDate' => $newStartDate->format('Y-m-d H:i:s'),
            'endDate' => $newEndDate?->format('Y-m-d H:i:s'),
            'email' => $parentDimensionContent->getEmail(),
            'phoneNumber' => $parentDimensionContent->getPhoneNumber(),
            'location' => $parentDimensionContent->getLocation()?->getId(),
            'showAuthor' => $parentDimensionContent->getShowAuthor(),
            'showDate' => $parentDimensionContent->getShowDate(),
        ];

        // Copy media if exists
        if ($image = $parentDimensionContent->getImage()) {
            $data['image'] = ['id' => $image->getId()];
        }

        if ($pdf = $parentDimensionContent->getPdf()) {
            $data['pdf'] = ['id' => $pdf->getId()];
        }

        // Copy speaker if exists
        if ($speaker = $parentDimensionContent->getSpeaker()) {
            $data['speaker'] = $speaker->getId();
        }

        // Copy images array
        if ($images = $parentDimensionContent->getImages()) {
            $data['images'] = $images;
        }

        // Persist content via ContentManager
        $this->contentManager->persist($newEvent, $data, [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ]);

        // Publish immediately
        $this->contentManager->applyTransition(
            $newEvent,
            [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ],
            WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
        );

        $this->entityManager->flush();

        return $newEvent;
    }
}