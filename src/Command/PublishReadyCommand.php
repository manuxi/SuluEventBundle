<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Command;

use Manuxi\SuluEventBundle\Import\AutoPublish\AutoPublisher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PublishReadyCommand extends Command
{
    public function __construct(private readonly AutoPublisher $autoPublisher)
    {
        parent::__construct();
    }

    public static function getDefaultName(): ?string
    {
        return 'sulu:event:import:publish-ready';
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Publishes imported events that are complete (rules: sulu_event.import.auto_publish)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only show what would be published and what is still missing')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Publish at most this many events in this run')
            ->setHelp(<<<'HELP'
                Publishes imported events by themselves, the way the "Publish" button of the admin does (all languages of
                <info>sulu_event.import.auto_publish.require.locales</info>, as the import user, with search, activity log and cache).
                Only events that match <info>auto_publish.when</info> and have everything <info>auto_publish.require</info> asks for
                are published. The others stay drafts; the table lists what they still lack.

                Events the sieve left out or that were rejected are never published. Neither are languages an editor unpublished or
                put in review. A published event with a new draft is only published again if <info>republish_changes</info> is on and no
                field of it was changed by hand.

                Look first:  <info>sulu:event:import:publish-ready --dry-run</info>

                Example for a daily run at 9 o'clock (crontab), after the import and the editorial run:

                    0 9 * * * cd /var/www/project && bin/adminconsole sulu:event:import:publish-ready

                Exit code: 0 if everything worked, 1 if an event could not be published.
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

        if (!$this->autoPublisher->isEnabled()) {
            $io->warning('The automatic publishing is switched off. Set sulu_event.import.auto_publish.enabled: true (see docs/import.en.md) to use it.');

            return Command::SUCCESS;
        }

        $io->title('Publish complete events' . ($dryRun ? ' (dry run, nothing is changed)' : ''));

        try {
            $result = $this->autoPublisher->publishReady($dryRun, null !== $limit ? (int) $limit : null);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->table(
            [$dryRun ? 'Would publish' : 'Published', 'Waiting', 'Skipped', 'Errors'],
            [[$result['published'], $result['waiting'], $result['skipped'], $result['errors']]]
        );

        $rows = [];
        foreach ($result['items'] as $item) {
            if ('skipped' === $item['result'] && !$io->isVerbose()) {
                continue;
            }

            $rows[] = [
                $item['title'],
                $item['source'],
                $item['startDate'] ?? '',
                $item['result'],
                implode(', ', $item['locales']),
                [] !== $item['missing'] ? 'missing: ' . implode(', ', $item['missing']) : ($item['note'] ?? ''),
            ];
        }

        if ([] !== $rows) {
            $io->table(['Event', 'Source', 'Start', 'Result', 'Languages', 'Missing / note'], $rows);
            if ($result['skipped'] > 0 && !$io->isVerbose()) {
                $io->note(\sprintf('%d skipped event(s) are not listed; add -v to see why.', $result['skipped']));
            }
        }

        if ($result['errors'] > 0) {
            $io->error(\sprintf('%d event(s) could not be published, see above.', $result['errors']));

            return Command::FAILURE;
        }

        $io->success($dryRun ? 'Dry run finished.' : 'Done.');

        return Command::SUCCESS;
    }
}
