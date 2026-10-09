<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Command;

use Manuxi\SuluEventBundle\Service\ImageTemplateDataCleanup;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'sulu:events:clean-image-data',
    description: 'Reduce the image stored in the event content data to its id (draft and live)'
)]
class CleanImageTemplateDataCommand extends Command
{
    public function __construct(
        private readonly ImageTemplateDataCleanup $cleanup,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Only list what would be changed, do not write anything'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        $io->title('Clean the event image data');
        if ($dryRun) {
            $io->note('Dry run: nothing is written.');
        }

        $result = $this->cleanup->run($dryRun);

        if ([] !== $result['entries']) {
            $io->table(
                ['Row', 'Event', 'Locale', 'Stage', 'Image before', 'Image after', 'Note'],
                \array_map(
                    static fn (array $entry): array => [
                        $entry['id'],
                        \substr($entry['event'], 0, 8),
                        $entry['locale'] ?? '-',
                        $entry['stage'],
                        $entry['before'],
                        $entry['after'],
                        $entry['note'] ?? '',
                    ],
                    $result['entries']
                )
            );
        }

        foreach ($result['failures'] as $failure) {
            $io->error(\sprintf(
                'Row %d (event %s, %s, %s): %s',
                $failure['id'],
                $failure['event'],
                $failure['locale'] ?? '-',
                $failure['stage'],
                $failure['error']
            ));
        }

        $io->writeln(\sprintf(
            '%d row(s) checked, %d %s, %d failed.',
            $result['scanned'],
            \count($result['entries']),
            $dryRun ? 'would be changed' : 'changed',
            \count($result['failures'])
        ));

        return [] === $result['failures'] ? Command::SUCCESS : Command::FAILURE;
    }
}
