<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\EventImportRun;
use Manuxi\SuluEventBundle\Import\EventImporter;
use Manuxi\SuluEventBundle\Import\ImportReport;
use Manuxi\SuluEventBundle\Import\ImportSettings;
use Manuxi\SuluEventBundle\Import\SourceRegistry;
use Manuxi\SuluEventBundle\Repository\EventImportRunRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportEventsCommand extends Command
{
    public function __construct(
        private readonly EventImporter $importer,
        private readonly SourceRegistry $sources,
        private readonly EventImportRunRepository $runRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ImportSettings $settings,
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): ?string
    {
        return 'sulu:event:import';
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Imports events from the configured sources (iCal feeds, pages with schema.org data) as drafts')
            ->addOption('source', 's', InputOption::VALUE_REQUIRED, 'Only this source, for example "ical.my-group" (see the list the command prints when no source is configured)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would happen, change nothing')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'At most this many events per source')
            ->setHelp(<<<'HELP'
                The command reads the sources under <info>sulu_event.import.sources</info> and creates or updates drafts. It never
                publishes. Run it with <info>--dry-run</info> first to see what it would do.

                Example for a daily run at 5 o'clock (crontab):

                    0 5 * * * cd /var/www/project && bin/adminconsole sulu:event:import

                Exit code: 0 if everything worked, 1 if a source or an event had an error.
                HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        $limit = $input->getOption('limit');
        if (null !== $limit && (!ctype_digit((string) $limit) || (int) $limit < 1)) {
            $io->error('--limit must be a positive whole number.');

            return Command::INVALID;
        }

        if (!$this->settings->enabled) {
            $io->warning('The import is switched off. Set sulu_event.import.enabled: true (and sulu_event.import.user) to use it.');

            return Command::SUCCESS;
        }

        if ([] === $this->sources->all()) {
            $io->warning('No import source is configured. Add sources under sulu_event.import.sources.ical / .jsonld.');

            return Command::SUCCESS;
        }

        $sourceKey = $input->getOption('source');
        if (null !== $sourceKey && !$this->sources->has((string) $sourceKey)) {
            $io->error(\sprintf('There is no import source "%s". Known sources: %s.', $sourceKey, implode(', ', $this->sources->getKeys())));

            return Command::INVALID;
        }

        $io->title('Event import' . ($dryRun ? ' (dry run, nothing is changed)' : ''));

        $startedAt = new \DateTimeImmutable();
        $run       = new EventImportRun($startedAt, null !== $sourceKey ? (string) $sourceKey : null, $dryRun);

        try {
            $report = $this->importer->import(null !== $sourceKey ? (string) $sourceKey : null, $dryRun, null !== $limit ? (int) $limit : null);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $this->printReport($io, $report);

        $run->finish(new \DateTimeImmutable(), $report->getCounts(), $report->toArray());
        $this->storeRun($run, $io);

        if ($report->hasErrors()) {
            $io->error(\sprintf('%d error(s), see above.', $report->getCounts()['errors']));

            return Command::FAILURE;
        }

        $io->success($dryRun ? 'Dry run finished.' : 'Import finished.');

        return Command::SUCCESS;
    }

    private function printReport(SymfonyStyle $io, ImportReport $report): void
    {
        $counts = $report->getCounts();
        $io->table(
            ['New', 'Changed', 'Unchanged', 'Locked', 'Vanished', 'Rejected', 'Errors', 'New locations'],
            [[$counts['created'], $counts['updated'], $counts['unchanged'], $counts['locked'], $counts['vanished'], $counts['rejected'], $counts['errors'], $counts['newLocations']]]
        );

        $rows = [];
        foreach ($report->getEntries() as $entry) {
            if (ImportReport::ACTION_UNCHANGED === $entry['action'] && [] === $entry['lockedFields'] && '' === $entry['detail']) {
                continue;
            }

            $detail = $entry['detail'];
            if ([] !== $entry['lockedFields']) {
                $detail = trim($detail . ' Locked (changed in the admin, not overwritten): ' . implode(', ', $entry['lockedFields']));
            }

            $rows[] = [$entry['action'], $entry['source'], $entry['title'] ?? $entry['externalId'] ?? '', $detail];
        }

        if ([] !== $rows) {
            $io->section('Details');
            $io->table(['Result', 'Source', 'Event', 'Note'], $rows);
        }

        if ([] !== $report->getNewLocations()) {
            $io->section('New locations (please have a look)');
            $io->table(
                ['Name', 'Time zone'],
                array_map(
                    static fn (array $location): array => [$location['name'], ($location['timezone'] ?? 'unknown') . ($location['certain'] ? '' : ' (guessed)')],
                    $report->getNewLocations()
                )
            );
        }

        foreach ($report->getWarnings() as $warning) {
            $io->warning(\sprintf('%s%s: %s', $warning['source'], null !== $warning['externalId'] ? ' / ' . $warning['externalId'] : '', $warning['message']));
        }
    }

    /**
     * The result of the run is kept for the admin and the MCP tools; a dry run is kept too (marked as such).
     */
    private function storeRun(EventImportRun $run, SymfonyStyle $io): void
    {
        try {
            // the entity manager may have been cleared by the import, so the run is added to the current one
            $this->runRepository->add($run);
            $this->entityManager->flush();
            $this->runRepository->prune();
        } catch (\Throwable $e) {
            $io->warning('The result of the run could not be stored: ' . $e->getMessage());
        }
    }
}
