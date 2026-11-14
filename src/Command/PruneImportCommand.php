<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Command;

use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Import\ImportPruner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PruneImportCommand extends Command
{
    public function __construct(private readonly ImportPruner $pruner)
    {
        parent::__construct();
    }

    public static function getDefaultName(): ?string
    {
        return 'sulu:event:import:prune';
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Moves imported events that the sieve left out or that were rejected, and that were never published, to the trash')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only list what would be removed')
            ->addOption('state', null, InputOption::VALUE_REQUIRED, 'Only events in this state: "filtered" or "rejected" (default: both)')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Do not ask (needed with --no-interaction)')
            ->setHelp(<<<'HELP'
                Events whose import entry is <info>filtered</info> (the rules under sulu_event.import.filters do not let the candidate pass,
                for example after the rules were tightened) or <info>rejected</info> (sulu_event_import_reject) stay in the database until you
                clean up. This command moves the ones that were <comment>never published</comment> to the trash of Sulu, like "delete" in the
                admin: they can be restored there. Events that were published, or that another source still maintains, are only listed
                ("please decide in the admin").

                Look first:  <info>sulu:event:import:prune --dry-run</info>
                HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $state = $input->getOption('state');
        if (null !== $state && !\in_array($state, [EventImport::STATE_FILTERED, EventImport::STATE_REJECTED], true)) {
            $io->error('--state must be "filtered" or "rejected".');

            return Command::INVALID;
        }
        $states = null !== $state ? [(string) $state] : [EventImport::STATE_FILTERED, EventImport::STATE_REJECTED];
        $dryRun = (bool) $input->getOption('dry-run');

        $items = $this->pruner->find($states);
        if ([] === $items) {
            $io->success('No event to clean up.');

            return Command::SUCCESS;
        }

        $io->table(
            ['Event', 'Source', 'State', 'Reason', 'Result'],
            array_map(
                static fn (array $item): array => [$item['title'], $item['source'], $item['state'], $item['reason'] ?? '', $item['removable'] ? ($dryRun ? 'would go to the trash' : 'goes to the trash') : 'stays: ' . $item['note']],
                $items
            )
        );

        $removable = array_values(array_filter($items, static fn (array $item): bool => $item['removable']));
        $kept      = \count($items) - \count($removable);
        if ($kept > 0) {
            $io->note(\sprintf('%d event(s) stay: they were published or another source maintains them. Please decide in the admin.', $kept));
        }

        if ($dryRun || [] === $removable) {
            $io->success($dryRun ? \sprintf('Dry run: %d event(s) would go to the trash.', \count($removable)) : 'Nothing to move to the trash.');

            return Command::SUCCESS;
        }

        if (!$input->getOption('force')) {
            if (!$input->isInteractive()) {
                $io->error('Not removing anything without a confirmation. Run it interactively, or add --force.');

                return Command::FAILURE;
            }

            if (!$io->confirm(\sprintf('Move %d event(s) to the trash (they can be restored there)?', \count($removable)), false)) {
                $io->warning('Aborted, nothing was removed.');

                return Command::SUCCESS;
            }
        }

        $removed = 0;
        foreach ($removable as $item) {
            if ($this->pruner->remove($item['uuid'])) {
                ++$removed;
            }
        }

        $io->success(\sprintf('%d event(s) moved to the trash.', $removed));

        return Command::SUCCESS;
    }
}
