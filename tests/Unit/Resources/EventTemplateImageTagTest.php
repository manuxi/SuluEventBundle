<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;

/**
 * The image of an event lives in the image_id column (EventUnlocalizedDataMapper). The template property is tagged so
 * Sulu's TemplateDataMapper does not store the media object a second time in templateData.
 */
class EventTemplateImageTagTest extends TestCase
{
    private const SKIP_TAG = 'sulu_content.skip_template_data_mapper';
    private const NAMESPACE = 'http://schemas.sulu.io/template/template';

    /**
     * @dataProvider provideTemplates
     */
    public function testImagePropertyIsSkippedByTheTemplateDataMapper(string $file): void
    {
        $document = new \DOMDocument();
        $this->assertTrue($document->load($file), $file);

        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('t', self::NAMESPACE);

        $properties = $xpath->query('//t:property[@name="image" and @type="single_media_upload"]');
        $this->assertNotFalse($properties);
        $this->assertSame(1, $properties->length, 'expected exactly one image upload property');

        $tags = $xpath->query(\sprintf('t:tag[@name="%s"]', self::SKIP_TAG), $properties->item(0));
        $this->assertNotFalse($tags);
        $this->assertSame(1, $tags->length, 'image property must carry the skip tag');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideTemplates(): iterable
    {
        foreach (['event', 'event_basic', 'event_detailed'] as $template) {
            yield $template => [\dirname(__DIR__, 3) . '/src/Resources/config/templates/events/' . $template . '.xml'];
        }
    }
}
