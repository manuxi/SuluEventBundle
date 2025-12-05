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
            // Get unlocalizedDimensionContent for recurrence, startDate, endDate
            $unlocalizedDimensionContent = $this->getUnlocalizedDimensionContent($event);

            if (!$unlocalizedDimensionContent) {
                $io->warning(sprintf('No unlocalized dimension content for event #%d', $event->getId()));
                $errors++;
                continue;
            }

            $recurrence = $unlocalizedDimensionContent->getRecurrence();
            if (!$recurrence || !$recurrence->getIsRecurring()) {
                continue;
            }

            // Get title from localized dimension content for display
            $dimensionContent = $this->contentManager->resolve($event, [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
            ]);

            $title = $dimensionContent instanceof EventDimensionContent
                ? $dimensionContent->getTitle()
                : 'Event #' . $event->getId();

            $io->writeln(sprintf('Processing: %s (ID: %d)', $title, $event->getId()));

            try {
                // Generate occurrences - NOW with unlocalizedDimensionContent!
                $occurrences = $this->recurrenceGenerator->generateOccurrences(
                    $recurrence,
                    $unlocalizedDimensionContent,  // ✅ NEW: Must pass this!
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
                    $newEvent = $this->createEventOccurrence(
                        $event,
                        $unlocalizedDimensionContent,
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
     * Check if occurrence already exists for this date
     */
    private function occurrenceExists(Event $parentEvent, \DateTimeInterface $date): bool
    {
        // Check if there's any event with same parent and start date
        $events = $this->eventRepository->findBy([]);

        foreach ($events as $event) {
            $unlocalizedDimensionContent = $this->getUnlocalizedDimensionContent($event);
            if (!$unlocalizedDimensionContent) {
                continue;
            }

            $startDate = $unlocalizedDimensionContent->getStartDate();
            if ($startDate && $startDate->format('Y-m-d') === $date->format('Y-m-d')) {
                // Found existing occurrence
                return true;
            }
        }

        return false;
    }

    /**
     * Create new event occurrence based on parent event
     */
    private function createEventOccurrence(
        Event $parentEvent,
        EventDimensionContent $parentUnlocalizedDimensionContent,
        \DateTimeInterface $occurrenceDate,
        string $locale
    ): Event {
        // Create new event entity
        $newEvent = new Event();
        $this->entityManager->persist($newEvent);
        $this->entityManager->flush(); // Need ID for ContentManager

        // Calculate duration from parent event
        $parentStartDate = $parentUnlocalizedDimensionContent->getStartDate();
        $parentEndDate = $parentUnlocalizedDimensionContent->getEndDate();

        $duration = null;
        if ($parentStartDate && $parentEndDate) {
            $duration = $parentStartDate->diff($parentEndDate);
        }

        // Calculate new end date
        $newStartDate = \DateTimeImmutable::createFromMutable($occurrenceDate);
        $newEndDate = $duration ? $newStartDate->add($duration) : null;

        // Get parent localized content
        $parentDimensionContent = $this->contentManager->resolve($parentEvent, [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_LIVE,
        ]);

        if (!$parentDimensionContent instanceof EventDimensionContent) {
            throw new \RuntimeException('Could not resolve parent dimension content');
        }

        // Build data array from parent
        $data = [
            'title' => $parentDimensionContent->getTitle(),
            'subtitle' => $parentDimensionContent->getSubtitle(),
            'summary' => $parentDimensionContent->getSummary(),
            'text' => $parentDimensionContent->getText(),
            'footer' => $parentDimensionContent->getFooter(),
            'type' => $parentUnlocalizedDimensionContent->getType(),
            'startDate' => $newStartDate->format('Y-m-d H:i:s'),
            'endDate' => $newEndDate?->format('Y-m-d H:i:s'),
            'email' => $parentUnlocalizedDimensionContent->getEmail(),
            'phoneNumber' => $parentUnlocalizedDimensionContent->getPhoneNumber(),
            'location' => $parentUnlocalizedDimensionContent->getLocation()?->getId(),
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

    /**
     * Get unlocalized dimension content from event
     */
    private function getUnlocalizedDimensionContent(Event $event): ?EventDimensionContent
    {
        foreach ($event->getDimensionContents() as $dc) {
            if ($dc->getLocale() === null
                && $dc->getStage() === DimensionContentInterface::STAGE_DRAFT
                && $dc->getVersion() === DimensionContentInterface::CURRENT_VERSION
            ) {
                return $dc;
            }
        }

        return null;
    }
}