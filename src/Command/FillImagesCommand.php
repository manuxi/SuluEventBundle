<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Command;

use Manuxi\SuluEventBundle\Import\ImageFiller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FillImagesCommand extends Command
{
    public function __construct(private readonly ImageFiller $filler)
    {
        parent::__construct();
    }

    public static function getDefaultName(): ?string
    {
        return 'sulu:event:fill-images';
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Gives the events and places that have no image the image of their topic (sulu_event.import.category_images) or of the places (sulu_event.import.location_image)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only list what would get an image')
            ->setHelp(<<<'HELP'
                Never replaces an image. An event takes the image of its first topic that has one; a published event gets a draft
                with the image and is live with it after it was published again. Safe to run again (the import runs it for the
                events it creates anyway; this is for the others).
                HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $result = $this->filler->fill($dryRun);

        foreach ($result['warnings'] as $warning) {
            $io->warning($warning);
        }
        if ([] !== $result['events']) {
            $io->section($dryRun ? 'Events that would get an image' : 'Events that got an image');
            $io->listing($result['events']);
        }
        if ([] !== $result['locations']) {
            $io->section($dryRun ? 'Places that would get an image' : 'Places that got an image');
            $io->listing($result['locations']);
        }

        $io->success(\sprintf('%d event(s) and %d place(s)%s.', \count($result['events']), \count($result['locations']), $dryRun ? ' would get an image (--dry-run: nothing changed)' : ' got an image'));

        return Command::SUCCESS;
    }
}
