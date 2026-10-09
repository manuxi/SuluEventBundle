<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Content\DataMapper\EventUnlocalizedDataMapper;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FieldMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TagMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TypedFormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\MetadataInterface;
use Sulu\Bundle\AdminBundle\Metadata\MetadataProviderInterface;
use Sulu\Bundle\AdminBundle\Metadata\MetadataProviderRegistry;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Content\Application\ContentDataMapper\DataMapper\TemplateDataMapper;

/**
 * Runs Sulu's TemplateDataMapper and the bundle's EventUnlocalizedDataMapper over a localized and an unlocalized
 * dimension content, the way the content manager does when it persists data.
 */
class EventTemplateDataFlowTest extends TestCase
{
    private const MEDIA_ID = 241;

    private MediaInterface $media;
    private EventDimensionContent $unlocalized;
    private EventDimensionContent $localized;
    private TemplateDataMapper $templateDataMapper;
    private EventUnlocalizedDataMapper $eventDataMapper;

    protected function setUp(): void
    {
        $this->media = $this->createMock(MediaInterface::class);
        $this->media->method('getId')->willReturn(self::MEDIA_ID);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getReference')->willReturn($this->media);

        $event = new Event();
        $this->unlocalized = new EventDimensionContent($event);
        $this->localized = new EventDimensionContent($event);
        $this->localized->setLocale('de');

        $this->unlocalized->setImage($this->media);
        $this->localized->setImage($this->media);

        $this->templateDataMapper = $this->createTemplateDataMapper(false);
        $this->eventDataMapper = new EventUnlocalizedDataMapper($entityManager);
    }

    /**
     * Same call as `ContentManager::persist($event, ['template' => ..., 'seo' => [...]], ...)` in a script or tool
     * that only wants to change a few fields: the image is not part of the data.
     *
     * @dataProvider provideStoredImageTemplateData
     *
     * @param array<string, mixed> $storedTemplateData
     */
    public function testPartialUpdateWithoutImageKeepsTheImage(array $storedTemplateData): void
    {
        $this->localized->setTemplateData($storedTemplateData);
        $this->localized->setImage($this->media);

        $this->map(['template' => 'event', 'summary' => 'New summary']);

        $this->assertSame($this->media, $this->localized->getImage(), 'image of the localized row');
        $this->assertSame($this->media, $this->unlocalized->getImage(), 'image of the unlocalized row');
        $this->assertSame('New summary', $this->localized->getSummary());
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function provideStoredImageTemplateData(): iterable
    {
        yield 'no image in the template data' => [['summary' => 'Old summary']];
        yield 'image id only' => [['summary' => 'Old summary', 'image' => ['id' => self::MEDIA_ID]]];
        yield 'full media object' => [[
            'summary' => 'Old summary',
            'image' => ['id' => self::MEDIA_ID, 'url' => '/media/241/image.jpg', 'thumbnails' => ['sulu-400x400' => '/a.jpg'], 'mimeType' => 'image/jpeg'],
        ]];
    }

    /**
     * What the MCP update tool sends for `{"summary": "..."}`: the template key and that one field.
     */
    public function testSummaryOnlyUpdateKeepsImageLocationDatesPdfAndSpeaker(): void
    {
        $location = new Location();
        $pdf = $this->createMock(MediaInterface::class);
        $speaker = $this->createMock(ContactInterface::class);
        $start = new \DateTimeImmutable('2026-11-05T09:00:00');
        $end = new \DateTimeImmutable('2026-11-05T17:00:00');

        foreach ([$this->unlocalized, $this->localized] as $row) {
            $row->setType('conference');
            $row->setStartDate($start);
            $row->setEndDate($end);
            $row->setLocation($location);
            $row->setPdf($pdf);
            $row->setSpeaker($speaker);
        }
        $this->localized->setTemplateData([
            'summary' => 'Old summary',
            'startDate' => '2026-11-05T09:00:00',
            'endDate' => '2026-11-05T17:00:00',
            'pdf' => ['id' => 5],
            'speaker' => 7,
        ]);

        $this->map(['template' => 'event', 'summary' => 'New summary']);

        foreach ([$this->unlocalized, $this->localized] as $row) {
            $this->assertSame($this->media, $row->getImage());
            $this->assertSame($location, $row->getLocation());
            $this->assertSame($pdf, $row->getPdf());
            $this->assertSame($speaker, $row->getSpeaker());
            $this->assertSame('conference', $row->getType());
            $this->assertEquals($start, $row->getStartDate());
            $this->assertEquals($end, $row->getEndDate());
        }
        $this->assertSame('New summary', $this->localized->getSummary());
    }

    public function testFullUpdateSetsTheImageFromTheFormValue(): void
    {
        $this->unlocalized->setImage(null);
        $this->localized->setImage(null);

        $this->map(['template' => 'event', 'image' => ['id' => self::MEDIA_ID, 'url' => '/media/241/image.jpg']]);

        $this->assertSame($this->media, $this->localized->getImage());
        $this->assertSame($this->media, $this->unlocalized->getImage());
    }

    public function testRemovingTheImageClearsBothRows(): void
    {
        $this->map(['template' => 'event', 'image' => null]);

        $this->assertNull($this->localized->getImage());
        $this->assertNull($this->unlocalized->getImage());
    }

    public function testSkippedImagePropertyStaysOutOfTheTemplateData(): void
    {
        if (!\defined(TemplateDataMapper::class . '::SKIP_TAG')) {
            $this->markTestSkipped('This sulu/sulu version has no TemplateDataMapper::SKIP_TAG.');
        }

        $this->templateDataMapper = $this->createTemplateDataMapper(true);

        $this->map(['template' => 'event', 'summary' => 'New summary', 'image' => ['id' => self::MEDIA_ID, 'url' => '/media/241/image.jpg']]);

        $this->assertArrayNotHasKey('image', $this->localized->getTemplateData());
        $this->assertArrayNotHasKey('image', $this->unlocalized->getTemplateData());
        $this->assertSame('New summary', $this->localized->getTemplateData()['summary']);
        $this->assertSame($this->media, $this->localized->getImage());
        $this->assertSame($this->media, $this->unlocalized->getImage());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function map(array $data): void
    {
        $this->templateDataMapper->map($this->unlocalized, $this->localized, $data);
        $this->eventDataMapper->map($this->unlocalized, $this->localized, $data);
    }

    private function createTemplateDataMapper(bool $skipImage): TemplateDataMapper
    {
        $form = new FormMetadata();
        foreach (['title', 'summary', 'image'] as $name) {
            $field = new FieldMetadata($name);
            $field->setMultilingual(true);

            if ($skipImage && 'image' === $name) {
                $tag = new TagMetadata();
                $tag->setName(TemplateDataMapper::SKIP_TAG);
                $field->addTag($tag);
            }

            $form->addItem($field);
        }

        $typedForm = new TypedFormMetadata();
        $typedForm->addForm('event', $form);
        $typedForm->setDefaultType('event');

        $provider = new class($typedForm) implements MetadataProviderInterface {
            public function __construct(private readonly TypedFormMetadata $typedForm)
            {
            }

            public function getMetadata(string $key, string $locale, array $metadataOptions): MetadataInterface
            {
                return $this->typedForm;
            }
        };

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($provider);

        return new TemplateDataMapper(new MetadataProviderRegistry($container));
    }
}
