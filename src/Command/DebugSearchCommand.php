<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Command;

use CmsIg\Seal\EngineInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugSearchCommand extends Command
{
    public function __construct(
        private readonly EngineInterface $engine,
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): ?string
    {
        return 'sulu:event:debug-search';
    }

    protected function configure(): void
    {
        $this->setDescription('Debug SEAL search index for events');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('SEAL Search Index Debug');

        // Search in admin index
        $io->section('Searching in admin index');

        try {
            $searchBuilder = $this->engine->createSearchBuilder('admin');
            $result = $searchBuilder->limit(100)->getResult();

            $io->info('Total results: '.$result->total());

            foreach ($result as $doc) {
                $io->writeln('---');
                $io->writeln('ID: '.($doc['id'] ?? 'N/A'));
                $io->writeln('Title: '.($doc['title'] ?? 'N/A'));
                $io->writeln('Locale: '.($doc['locale'] ?? 'N/A'));
                $io->writeln('Published: '.($doc['published'] ?? 'N/A'));
                $io->writeln('Resource ID: '.($doc['resourceId'] ?? 'N/A'));
                $io->writeln('Start Date: '.($doc['startDate'] ?? 'N/A'));
            }

            if (0 === $result->total()) {
                $io->warning('No documents found in admin index!');
                $io->note('Try creating/editing an event to trigger indexing.');
            }
        } catch (\Exception $e) {
            $io->error('Error searching: '.$e->getMessage());
        }

        // Search in website index
        $io->section('Searching in website index');

        try {
            $searchBuilder = $this->engine->createSearchBuilder('website');
            $result = $searchBuilder->limit(100)->getResult();

            $io->info('Total results: '.$result->total());

            foreach ($result as $doc) {
                $io->writeln('---');
                $io->writeln('ID: '.($doc['id'] ?? 'N/A'));
                $io->writeln('Title: '.($doc['title'] ?? 'N/A'));
                $io->writeln('Locale: '.($doc['locale'] ?? 'N/A'));
                $io->writeln('URL: '.($doc['url'] ?? 'N/A'));
            }

            if (0 === $result->total()) {
                $io->warning('No documents found in website index!');
            }
        } catch (\Exception $e) {
            $io->error('Error searching: '.$e->getMessage());
        }

        // Count documents
        $io->section('Document counts');
        $io->writeln('admin: '.$this->engine->countDocuments('admin'));
        $io->writeln('website: '.$this->engine->countDocuments('website'));

        return Command::SUCCESS;
    }
}
