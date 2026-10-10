<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import\Http;

use Manuxi\SuluEventBundle\Import\Http\RobotsTxt;
use PHPUnit\Framework\TestCase;

class RobotsTxtTest extends TestCase
{
    private const AGENT = 'SuluEventBundle-Import (+https://example.com/contact)';

    public function testEverythingIsAllowedWithoutRules(): void
    {
        $this->assertTrue((new RobotsTxt('', self::AGENT))->isAllowed('/events'));
        $this->assertTrue(RobotsTxt::allowAll()->isAllowed('/anything'));
        $this->assertFalse(RobotsTxt::disallowAll()->isAllowed('/'));
    }

    public function testDisallowForAllRobots(): void
    {
        $robots = new RobotsTxt("User-agent: *\nDisallow: /private/\nDisallow: /search\n", self::AGENT);

        $this->assertFalse($robots->isAllowed('/private/page'));
        $this->assertFalse($robots->isAllowed('/search?q=php'));
        $this->assertTrue($robots->isAllowed('/events'));
    }

    public function testAllowBeatsDisallowWithTheLongerMatchAndWinsTies(): void
    {
        $robots = new RobotsTxt("User-agent: *\nDisallow: /events/\nAllow: /events/public/\nDisallow: /tie\nAllow: /tie\n", self::AGENT);

        $this->assertFalse($robots->isAllowed('/events/secret'));
        $this->assertTrue($robots->isAllowed('/events/public/list'));
        $this->assertTrue($robots->isAllowed('/tie'));
    }

    public function testGroupOfOurOwnProductWinsOverTheStarGroup(): void
    {
        $robots = new RobotsTxt(
            "User-agent: *\nDisallow: /\n\nUser-agent: SuluEventBundle-Import\nAllow: /\nDisallow: /admin\n",
            self::AGENT
        );

        $this->assertTrue($robots->isAllowed('/events'));
        $this->assertFalse($robots->isAllowed('/admin'));
    }

    public function testWholeSiteDisallowed(): void
    {
        $robots = new RobotsTxt("User-agent: *\nDisallow: /\n", self::AGENT);

        $this->assertFalse($robots->isAllowed('/'));
        $this->assertFalse($robots->isAllowed('/calendar.ics'));
    }

    public function testWildcardsAndEndAnchor(): void
    {
        $robots = new RobotsTxt("User-agent: *\nDisallow: /*.ics$\nDisallow: /tmp*/x\n", self::AGENT);

        $this->assertFalse($robots->isAllowed('/feeds/all.ics'));
        $this->assertTrue($robots->isAllowed('/feeds/all.ics.html'));
        $this->assertFalse($robots->isAllowed('/tmp123/x'));
    }

    public function testEmptyDisallowAllowsAndCommentsAreIgnored(): void
    {
        $robots = new RobotsTxt("# keep out\nUser-agent: *  # all\nDisallow:\nCrawl-delay: 10\n", self::AGENT);

        $this->assertTrue($robots->isAllowed('/anything'));
    }

    public function testSeveralUserAgentLinesShareTheRules(): void
    {
        $robots = new RobotsTxt("User-agent: googlebot\nUser-agent: sulueventbundle-import\nDisallow: /nope\n", self::AGENT);

        $this->assertFalse($robots->isAllowed('/nope'));
        $this->assertTrue($robots->isAllowed('/yes'));
    }
}
