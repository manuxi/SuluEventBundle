<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Command;

use Manuxi\SuluEventBundle\Command\CleanImageTemplateDataCommand;
use Manuxi\SuluEventBundle\Service\ImageTemplateDataCleanup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CleanImageTemplateDataCommandTest extends TestCase
{
    public function testDryRunIsPassedOnAndListsTheRows(): void
    {
        $cleanup = $this->createMock(ImageTemplateDataCleanup::class);
        $cleanup->expects($this->once())->method('run')->with(true)->willReturn([
            'scanned' => 5,
            'entries' => [[
                'id' => 12,
                'event' => '01a0e097-0000-7000-8000-000000000000',
                'locale' => 'de',
                'stage' => 'draft',
                'before' => '{id, url, thumbnails, mimeType}',
                'after' => '{"id":241}',
                'note' => null,
            ]],
            'failures' => [],
        ]);

        $tester = new CommandTester(new CleanImageTemplateDataCommand($cleanup));
        $exitCode = $tester->execute(['--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('Dry run', $output);
        $this->assertStringContainsString('01a0e097', $output);
        $this->assertStringContainsString('{"id":241}', $output);
        $this->assertStringContainsString('5 row(s) checked, 1 would be changed, 0 failed.', $output);
    }

    public function testFailuresAreShownAndSetTheExitCode(): void
    {
        $cleanup = $this->createMock(ImageTemplateDataCleanup::class);
        $cleanup->expects($this->once())->method('run')->with(false)->willReturn([
            'scanned' => 2,
            'entries' => [],
            'failures' => [[
                'id' => 7,
                'event' => '01a0e097-0000-7000-8000-000000000000',
                'locale' => 'de',
                'stage' => 'live',
                'error' => 'Syntax error',
            ]],
        ]);

        $tester = new CommandTester(new CleanImageTemplateDataCommand($cleanup));
        $exitCode = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);

        // SymfonyStyle wraps the error block to the terminal width (80 columns on CI), so the line breaks vary
        $output = \preg_replace('/\s+/', ' ', $tester->getDisplay());
        $this->assertStringContainsString('Row 7', $output);
        $this->assertStringContainsString('Syntax error', $output);
        $this->assertStringContainsString('2 row(s) checked, 0 changed, 1 failed.', $output);
    }
}
