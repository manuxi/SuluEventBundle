<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Command;

use Sulu\Bundle\AdminBundle\Teaser\Provider\TeaserProviderPoolInterface;
use Sulu\Bundle\AdminBundle\Teaser\TeaserManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * DEBUG COMMAND - Remove after debugging!
 */
#[AsCommand(
    name: 'sulu:debug:teaser',
    description: 'Debug teaser providers and test teaser loading',
)]
class DebugTeaserCommand extends Command
{
    public function __construct(
        private TeaserProviderPoolInterface $providerPool,
        private TeaserManagerInterface $teaserManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::OPTIONAL, 'Teaser type to test (e.g., events, pages, articles)')
            ->addArgument('id', InputArgument::OPTIONAL, 'ID to load')
            ->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'Locale', 'de');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Teaser Provider Debug');

        $configurations = $this->providerPool->getConfiguration();

        $io->section('Registered Teaser Providers');
        $aliases = array_keys($configurations);
        $io->listing($aliases);

        $type = $input->getArgument('type');
        $id = $input->getArgument('id');
        $locale = $input->getOption('locale');

        if ($type && $id) {
            $io->section("Testing Teaser Loading: {$type}:{$id}");

            if (!$this->providerPool->hasProvider($type)) {
                $io->error("Provider '{$type}' not found!");
                $io->note('Available providers: ' . implode(', ', $aliases));
                return Command::FAILURE;
            }

            $io->success("Provider '{$type}' exists");

            $provider = $this->providerPool->getProvider($type);
            $io->info('Provider class: ' . get_class($provider));

            $io->section('Testing Provider::find() directly');
            $teasers = $provider->find([$id], $locale);

            if (empty($teasers)) {
                $io->warning('Provider returned empty array!');
            } else {
                $io->success(sprintf('Provider returned %d teaser(s)', count($teasers)));
                foreach ($teasers as $teaser) {
                    $io->table(
                        ['Property', 'Value'],
                        [
                            ['ID', $teaser->getId()],
                            ['Type', $teaser->getType()],
                            ['Title', $teaser->getTitle()],
                            ['URL', $teaser->getUrl()],
                            ['Media ID', $teaser->getMediaId() ?? 'null'],
                            ['Description', mb_substr($teaser->getDescription(), 0, 50) . '...'],
                        ]
                    );
                }
            }

            $io->section('Testing TeaserManager::find()');
            $items = [['type' => $type, 'id' => $id]];
            $io->note('Input: ' . json_encode($items));

            $managerTeasers = $this->teaserManager->find($items, $locale);

            if (empty($managerTeasers)) {
                $io->warning('TeaserManager returned empty array!');
            } else {
                $io->success(sprintf('TeaserManager returned %d teaser(s)', count($managerTeasers)));
            }
        }

        return Command::SUCCESS;
    }
}