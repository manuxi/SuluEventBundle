<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Twig;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Service\SocialShareGenerator;
use Manuxi\SuluEventBundle\Twig\SocialShareTwigExtension;
use PHPUnit\Framework\TestCase;

class SocialShareTwigExtensionTest extends TestCase
{
    private SocialShareTwigExtension $extension;
    private SocialShareGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = $this->createMock(SocialShareGenerator::class);
        $this->extension = new SocialShareTwigExtension($this->generator);
    }

    public function testFunctionsDelegateToGenerator(): void
    {
        $event = new Event();
        $this->generator->expects($this->once())->method('generateShareLinks')->with($event, 'en')->willReturn(['fb' => 'link']);
        $this->generator->expects($this->once())->method('generateOpenGraphTags')->with($event, 'en')->willReturn(['og:title' => 'hi']);

        $this->assertEquals(['fb' => 'link'], $this->extension->getSocialShares($event, 'en'));
        $this->assertEquals(['og:title' => 'hi'], $this->extension->getOpenGraphTags($event, 'en'));
    }
}
