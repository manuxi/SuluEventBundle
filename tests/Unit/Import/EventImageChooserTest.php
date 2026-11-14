<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import;

use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Import\EventImageChooser;
use Manuxi\SuluEventBundle\Import\ImportSettings;
use Manuxi\SuluEventBundle\Import\Resolver\MediaResolver;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

/**
 * The image of an event, from the best to the worst: photo of the series, photo of the place, placeholder of the topic.
 */
class EventImageChooserTest extends TestCase
{
    public function testPhotoOfTheSeriesWins(): void
    {
        $this->assertSame('ifa.jpg', $this->chooser()->reference('ifa', $this->placeWithImage(555), ['ai']));
    }

    public function testPhotoOfThePlaceBeatsThePlaceholderOfTheTopic(): void
    {
        $this->assertSame(555, $this->chooser()->reference('other', $this->placeWithImage(555), ['ai']));
    }

    public function testPlaceholderOfThePlaceIsNoPhoto(): void
    {
        // the image all places get is the placeholder, not a photo of the place: the topic image it is
        $this->assertSame('topic-ai.jpg', $this->chooser()->reference(null, $this->placeWithImage(900), ['unknown', 'ai']));
    }

    public function testWithoutAnythingBetterThePlaceholderOfTheFirstTopicThatHasOne(): void
    {
        $this->assertSame('topic-ai.jpg', $this->chooser()->reference(null, null, ['unknown', 'ai']));
        $this->assertNull($this->chooser()->reference(null, null, ['unknown']));
    }

    public function testPlaceholders(): void
    {
        $chooser = $this->chooser();

        $this->assertTrue($chooser->isPlaceholder(901), 'topic image');
        $this->assertTrue($chooser->isPlaceholder(900), 'image of the places');
        $this->assertFalse($chooser->isPlaceholder(555));
    }

    public function testGeneratedCardsAreAPlaceholderBelowEveryPhoto(): void
    {
        $chooser = $this->chooser(['events.cards']);

        $this->assertTrue($chooser->isCard(777));
        $this->assertTrue($chooser->isPlaceholder(777), 'a photo replaces the card');
        $this->assertFalse($chooser->isCard(901), 'the image of a topic is a placeholder, but no card');
        $this->assertFalse($chooser->isCard(555));
        $this->assertFalse($this->chooser()->isPlaceholder(777), 'without placeholder_collections a card is an image like any other');
    }

    public function testSeriesOfTheMatchKey(): void
    {
        $this->assertSame('ifa', EventImageChooser::seriesOfMatchKey('ifa-2027'));
        $this->assertSame('web-summit', EventImageChooser::seriesOfMatchKey('web-summit-2026'));
        $this->assertNull(EventImageChooser::seriesOfMatchKey('no-year'));
        $this->assertNull(EventImageChooser::seriesOfMatchKey(null));
    }

    /**
     * @param string[] $cards placeholder_collections (the media 777 lies in "events.cards")
     */
    private function chooser(array $cards = []): EventImageChooser
    {
        $settings = new ImportSettings(true, 'import', false, '/events', null, 'default', 'event', ['ai' => 'topic-ai.jpg'], [], 'place.jpg', ['ifa' => 'ifa.jpg'], [], $cards);

        $media = $this->createStub(MediaResolver::class);
        $media->method('isInSystemCollections')->willReturnCallback(static fn (int $id, array $keys): bool => 777 === $id && \in_array('events.cards', $keys, true));
        $media->method('resolve')->willReturnCallback(static fn (int|string $reference): ?int => match ($reference) {
            'topic-ai.jpg' => 901,
            'place.jpg'    => 900,
            default        => null,
        });

        return new EventImageChooser($settings, $media);
    }

    private function placeWithImage(int $mediaId): Location
    {
        $image = $this->createStub(MediaInterface::class);
        $image->method('getId')->willReturn($mediaId);

        return (new Location())->setImage($image);
    }
}
