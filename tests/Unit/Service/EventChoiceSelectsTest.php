<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Entity\AttendanceMode;
use Manuxi\SuluEventBundle\Entity\EventStatus;
use Manuxi\SuluEventBundle\Service\AttendanceModeSelect;
use Manuxi\SuluEventBundle\Service\EventStatusSelect;
use Manuxi\SuluEventBundle\Service\LanguageSelect;
use Manuxi\SuluEventBundle\Service\TimezoneSelect;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The selects of the event form (status, attendance mode, time zone, languages).
 */
class EventChoiceSelectsTest extends TestCase
{
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->translator = $this->createStub(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnCallback(static fn (string $id): string => '[' . $id . ']');
    }

    public function testStatusSelectListsEveryStatusWithATranslatedTitle(): void
    {
        $select = new EventStatusSelect($this->translator);

        $this->assertSame('scheduled', $select->getDefaultValue());
        $this->assertSame(EventStatus::values(), array_column($select->getValues(), 'name'));
        $this->assertSame(
            ['name' => 'moved_online', 'title' => '[sulu_event.status.moved_online]'],
            $select->getValues()[4]
        );
        $this->assertSame('[sulu_event.status.cancelled]', $select->getName('cancelled'));
        $this->assertSame('unknown', $select->getName('unknown'));
        $this->assertTrue($select->hasKey('postponed'));
        $this->assertFalse($select->hasKey('unknown'));
    }

    public function testAttendanceModeSelect(): void
    {
        $select = new AttendanceModeSelect($this->translator);

        $this->assertSame('offline', $select->getDefaultValue());
        $this->assertSame(AttendanceMode::values(), array_column($select->getValues(), 'name'));
        $this->assertSame('[sulu_event.attendance_mode.mixed]', $select->getName('mixed'));
    }

    public function testTimezoneSelectStartsWithAutomaticAndKnowsTheIanaZones(): void
    {
        $select = new TimezoneSelect($this->translator);
        $values = $select->getValues();

        $this->assertSame('auto', $select->getDefaultValue());
        $this->assertSame(['name' => 'auto', 'title' => '[sulu_event.timezone.auto]'], $values[0]);
        $this->assertContains(['name' => 'Europe/Berlin', 'title' => 'Europe/Berlin'], $values);
        $this->assertContains(['name' => 'America/Los_Angeles', 'title' => 'America/Los_Angeles'], $values);
        $this->assertContains(['name' => 'UTC', 'title' => 'UTC'], $values);
    }

    public function testLanguageSelectOffersTwoLetterCodesWithNamesInTheLanguageOfTheUser(): void
    {
        $translator = new class implements TranslatorInterface, \Symfony\Contracts\Translation\LocaleAwareInterface {
            public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
            {
                return $id;
            }

            public function getLocale(): string
            {
                return 'de';
            }

            public function setLocale(string $locale): void
            {
            }
        };

        $values = (new LanguageSelect($translator))->getValues();
        $byCode = array_column($values, 'title', 'name');

        $this->assertSame('Deutsch', $byCode['de']);
        $this->assertSame('Englisch', $byCode['en']);
        foreach (array_keys($byCode) as $code) {
            $this->assertMatchesRegularExpression('/^[a-z]{2}$/', (string) $code);
        }
    }
}
