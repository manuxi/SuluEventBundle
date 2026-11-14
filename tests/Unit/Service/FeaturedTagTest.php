<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Service\FeaturedTag;
use Manuxi\SuluEventBundle\Tests\Integration\Stub\StubTag;
use PHPUnit\Framework\TestCase;

class FeaturedTagTest extends TestCase
{
    public function testWithoutANameTheFeatureIsOffAndNothingIsFeatured(): void
    {
        foreach ([null, '', '   '] as $name) {
            $featuredTag = new FeaturedTag($name);

            $this->assertFalse($featuredTag->isEnabled());
            $this->assertNull($featuredTag->getName());
            $this->assertFalse($featuredTag->isFeatured(['Highlight']));
        }
    }

    public function testTheNameIsTrimmedAndComparedWithoutRegardToCase(): void
    {
        $featuredTag = new FeaturedTag('  Highlight ');

        $this->assertTrue($featuredTag->isEnabled());
        $this->assertSame('Highlight', $featuredTag->getName());
        $this->assertTrue($featuredTag->isFeatured(['Symfony', 'highlight']));
        $this->assertTrue($featuredTag->isFeatured([' HIGHLIGHT ']));
        $this->assertFalse($featuredTag->isFeatured(['Highlights', 'Symfony']));
        $this->assertFalse($featuredTag->isFeatured([]));
    }

    public function testTagsAreTakenFromObjectsWithAName(): void
    {
        $featuredTag = new FeaturedTag('Highlight');

        $this->assertTrue($featuredTag->isFeatured([new StubTag('Symfony'), new StubTag('highlight')]));
        $this->assertFalse($featuredTag->isFeatured([new StubTag('Symfony'), 12, null]));
    }
}
