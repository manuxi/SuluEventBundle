<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Service\EventUrlResolver;
use PHPUnit\Framework\TestCase;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Route\Domain\Model\Route;

class EventUrlResolverTest extends TestCase
{
    public function testFindUrlAsksTheWebspaceOfTheEvent(): void
    {
        $webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $webspaceManager->expects($this->once())
            ->method('findUrlByResourceLocator')
            ->with('/events/meetup', null, 'de', 'example')
            ->willReturn('https://example.com/events/meetup');

        $content = new EventDimensionContent(new Event());
        $content->setRoute(new Route('events', 'x', 'de', '/events/meetup'));
        $content->setMainWebspace('example');

        $this->assertSame('https://example.com/events/meetup', (new EventUrlResolver($webspaceManager))->findUrl($content, 'de'));
    }

    public function testFindUrlIsNullWithoutARoute(): void
    {
        $webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $webspaceManager->expects($this->never())->method('findUrlByResourceLocator');

        $this->assertNull((new EventUrlResolver($webspaceManager))->findUrl(new EventDimensionContent(new Event()), 'de'));
    }

    public function testFindSiteUrlAsksForTheStartPage(): void
    {
        $webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $webspaceManager->expects($this->once())
            ->method('findUrlByResourceLocator')
            ->with('/', null, 'en', null)
            ->willReturn('https://example.com/en');

        $this->assertSame('https://example.com/en', (new EventUrlResolver($webspaceManager))->findSiteUrl(new EventDimensionContent(new Event()), 'en'));
    }
}
