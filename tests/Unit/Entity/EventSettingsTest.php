<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\EventSettings;
use PHPUnit\Framework\TestCase;

class EventSettingsTest extends TestCase
{
    private EventSettings $settings;

    protected function setUp(): void
    {
        $this->settings = new EventSettings();
    }

    public function testToggleHeader(): void
    {
        $this->assertNull($this->settings->getToggleHeader());
        $this->settings->setToggleHeader(true);
        $this->assertTrue($this->settings->getToggleHeader());
    }

    public function testToggleHero(): void
    {
        $this->assertNull($this->settings->getToggleHero());
        $this->settings->setToggleHero(true);
        $this->assertTrue($this->settings->getToggleHero());
    }

    public function testToggleBreadcrumbs(): void
    {
        $this->assertNull($this->settings->getToggleBreadcrumbs());
        $this->settings->setToggleBreadcrumbs(false);
        $this->assertFalse($this->settings->getToggleBreadcrumbs());
    }

    public function testCalendarStartDay(): void
    {
        $this->assertEquals(1, $this->settings->getCalendarStartDay());
        $this->settings->setCalendarStartDay(0);
        $this->assertEquals(0, $this->settings->getCalendarStartDay());
    }

    public function testShowWeekNumbers(): void
    {
        $this->assertFalse($this->settings->getShowWeekNumbers());
        $this->settings->setShowWeekNumbers(true);
        $this->assertTrue($this->settings->getShowWeekNumbers());
    }

    public function testEventLimitPerDay(): void
    {
        $this->assertEquals(3, $this->settings->getEventLimitPerDay());
        $this->settings->setEventLimitPerDay(5);
        $this->assertEquals(5, $this->settings->getEventLimitPerDay());
    }

    public function testToggleCalendarView(): void
    {
        $this->assertFalse($this->settings->getToggleCalendarView());
        $this->settings->setToggleCalendarView(true);
        $this->assertTrue($this->settings->getToggleCalendarView());
    }

    public function testAllowedCalendarViews(): void
    {
        $this->assertEquals(['dayGridMonth'], $this->settings->getAllowedCalendarViews());
        $views = ['dayGridMonth', 'timeGridWeek', 'listMonth'];
        $this->settings->setAllowedCalendarViews($views);
        $this->assertEquals($views, $this->settings->getAllowedCalendarViews());
    }

    public function testEventsPerPage(): void
    {
        $this->assertEquals(12, $this->settings->getEventsPerPage());
        $this->settings->setEventsPerPage(20);
        $this->assertEquals(20, $this->settings->getEventsPerPage());
    }

    public function testDefaultSortOrder(): void
    {
        $this->assertEquals('start_date_asc', $this->settings->getDefaultSortOrder());
        $this->settings->setDefaultSortOrder('title_asc');
        $this->assertEquals('title_asc', $this->settings->getDefaultSortOrder());
    }

    public function testResourceKeyConstants(): void
    {
        $this->assertEquals('event_settings', EventSettings::RESOURCE_KEY);
        $this->assertEquals('config', EventSettings::FORM_KEY);
        $this->assertEquals('sulu.event.settings', EventSettings::SECURITY_CONTEXT);
    }
}