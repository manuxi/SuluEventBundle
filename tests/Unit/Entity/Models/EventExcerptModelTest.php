<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity\Models;

use Manuxi\SuluEventBundle\Entity\EventExcerpt;
use Manuxi\SuluEventBundle\Entity\Models\EventExcerptModel;
use Manuxi\SuluEventBundle\Repository\EventExcerptRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Bundle\TagBundle\Tag\TagManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class EventExcerptModelTest extends TestCase
{
    private EventExcerptModel $eventExcerptModel;

    private EventExcerptRepository|MockObject $eventExcerptRepository;
    private CategoryManagerInterface|MockObject $categoryManager;
    private TagManagerInterface|MockObject $tagManager;
    private MediaRepositoryInterface|MockObject $mediaRepository;

    protected function setUp(): void
    {
        $this->eventExcerptRepository = $this->createMock(EventExcerptRepository::class);
        $this->categoryManager = $this->createMock(CategoryManagerInterface::class);
        $this->tagManager = $this->createMock(TagManagerInterface::class);
        $this->mediaRepository = $this->createMock(MediaRepositoryInterface::class);

        $this->eventExcerptModel = new EventExcerptModel(
            $this->eventExcerptRepository,
            $this->categoryManager,
            $this->tagManager,
            $this->mediaRepository
        );
    }

    public function testUpdateEventExcerptWithBasicFields(): void
    {
        // Arrange
        $eventExcerpt = $this->createMock(EventExcerpt::class);
        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'locale' => 'en',
                    'title' => 'Excerpt Title',
                    'more' => 'Read more text',
                    'description' => 'Excerpt description',
                ],
            ],
        ]);

        $eventExcerpt->expects($this->once())
            ->method('setLocale')
            ->with('en');

        $eventExcerpt->expects($this->once())
            ->method('setTitle')
            ->with('Excerpt Title');

        $eventExcerpt->expects($this->once())
            ->method('setMore')
            ->with('Read more text');

        $eventExcerpt->expects($this->once())
            ->method('setDescription')
            ->with('Excerpt description');

        $this->eventExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventExcerpt)
            ->willReturn($eventExcerpt);

        // Act
        $result = $this->eventExcerptModel->updateEventExcerpt($eventExcerpt, $request);

        // Assert
        $this->assertSame($eventExcerpt, $result);
    }

    public function testUpdateEventExcerptWithCategories(): void
    {
        // Arrange
        $eventExcerpt = $this->createMock(EventExcerpt::class);
        $category1 = $this->createMock(CategoryInterface::class);
        $category2 = $this->createMock(CategoryInterface::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'categories' => [1, 2],
                ],
            ],
        ]);

        $eventExcerpt->expects($this->once())
            ->method('removeCategories');

        $this->categoryManager
            ->expects($this->once())
            ->method('findByIds')
            ->with([1, 2])
            ->willReturn([$category1, $category2]);

        $addCategoryCallCount = 0;
        $eventExcerpt->expects($this->exactly(2))
            ->method('addCategory')
            ->willReturnCallback(function ($category) use (&$addCategoryCallCount, $category1, $category2, $eventExcerpt) {
                ++$addCategoryCallCount;
                if (1 === $addCategoryCallCount) {
                    $this->assertSame($category1, $category);
                } elseif (2 === $addCategoryCallCount) {
                    $this->assertSame($category2, $category);
                }

                return $eventExcerpt;
            });

        $this->eventExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventExcerpt)
            ->willReturn($eventExcerpt);

        // Act
        $result = $this->eventExcerptModel->updateEventExcerpt($eventExcerpt, $request);

        // Assert
        $this->assertSame($eventExcerpt, $result);
    }

    public function testUpdateEventExcerptWithTags(): void
    {
        // Arrange
        $eventExcerpt = $this->createMock(EventExcerpt::class);
        $tag1 = $this->createMock(TagInterface::class);
        $tag2 = $this->createMock(TagInterface::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'tags' => ['Technology', 'Event'],
                ],
            ],
        ]);

        $eventExcerpt->expects($this->once())
            ->method('removeTags');

        $findTagCallCount = 0;
        $this->tagManager
            ->expects($this->exactly(2))
            ->method('findOrCreateByName')
            ->willReturnCallback(function ($name) use (&$findTagCallCount, $tag1, $tag2) {
                ++$findTagCallCount;
                if (1 === $findTagCallCount) {
                    $this->assertEquals('Technology', $name);

                    return $tag1;
                } elseif (2 === $findTagCallCount) {
                    $this->assertEquals('Event', $name);

                    return $tag2;
                }
            });

        $addTagCallCount = 0;
        $eventExcerpt->expects($this->exactly(2))
            ->method('addTag')
            ->willReturnCallback(function ($tag) use (&$addTagCallCount, $tag1, $tag2, $eventExcerpt) {
                ++$addTagCallCount;
                if (1 === $addTagCallCount) {
                    $this->assertSame($tag1, $tag);
                } elseif (2 === $addTagCallCount) {
                    $this->assertSame($tag2, $tag);
                }

                return $eventExcerpt;
            });

        $this->eventExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventExcerpt)
            ->willReturn($eventExcerpt);

        // Act
        $result = $this->eventExcerptModel->updateEventExcerpt($eventExcerpt, $request);

        // Assert
        $this->assertSame($eventExcerpt, $result);
    }

    public function testUpdateEventExcerptWithIcons(): void
    {
        // Arrange
        $eventExcerpt = $this->createMock(EventExcerpt::class);
        $icon1 = $this->createMock(Media::class);
        $icon2 = $this->createMock(Media::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'icon' => [
                        'ids' => [10, 20],
                    ],
                ],
            ],
        ]);

        $eventExcerpt->expects($this->once())
            ->method('removeIcons');

        $findIconCallCount = 0;
        $this->mediaRepository
            ->expects($this->exactly(2))
            ->method('findMediaById')
            ->willReturnCallback(function ($id) use (&$findIconCallCount, $icon1, $icon2) {
                ++$findIconCallCount;
                if (1 === $findIconCallCount) {
                    $this->assertEquals(10, $id);

                    return $icon1;
                } elseif (2 === $findIconCallCount) {
                    $this->assertEquals(20, $id);

                    return $icon2;
                }
            });

        $addIconCallCount = 0;
        $eventExcerpt->expects($this->exactly(2))
            ->method('addIcon')
            ->willReturnCallback(function ($icon) use (&$addIconCallCount, $icon1, $icon2, $eventExcerpt) {
                ++$addIconCallCount;
                if (1 === $addIconCallCount) {
                    $this->assertSame($icon1, $icon);
                } elseif (2 === $addIconCallCount) {
                    $this->assertSame($icon2, $icon);
                }

                return $eventExcerpt;
            });

        $this->eventExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventExcerpt)
            ->willReturn($eventExcerpt);

        // Act
        $result = $this->eventExcerptModel->updateEventExcerpt($eventExcerpt, $request);

        // Assert
        $this->assertSame($eventExcerpt, $result);
    }

    public function testUpdateEventExcerptWithImages(): void
    {
        // Arrange
        $eventExcerpt = $this->createMock(EventExcerpt::class);
        $image1 = $this->createMock(Media::class);
        $image2 = $this->createMock(Media::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'images' => [
                        'ids' => [30, 40],
                    ],
                ],
            ],
        ]);

        $eventExcerpt->expects($this->once())
            ->method('removeImages');

        $findImageCallCount = 0;
        $this->mediaRepository
            ->expects($this->exactly(2))
            ->method('findMediaById')
            ->willReturnCallback(function ($id) use (&$findImageCallCount, $image1, $image2) {
                ++$findImageCallCount;
                if (1 === $findImageCallCount) {
                    $this->assertEquals(30, $id);

                    return $image1;
                } elseif (2 === $findImageCallCount) {
                    $this->assertEquals(40, $id);

                    return $image2;
                }
            });

        $addImageCallCount = 0;
        $eventExcerpt->expects($this->exactly(2))
            ->method('addImage')
            ->willReturnCallback(function ($image) use (&$addImageCallCount, $image1, $image2, $eventExcerpt) {
                ++$addImageCallCount;
                if (1 === $addImageCallCount) {
                    $this->assertSame($image1, $image);
                } elseif (2 === $addImageCallCount) {
                    $this->assertSame($image2, $image);
                }

                return $eventExcerpt;
            });

        $this->eventExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventExcerpt)
            ->willReturn($eventExcerpt);

        // Act
        $result = $this->eventExcerptModel->updateEventExcerpt($eventExcerpt, $request);

        // Assert
        $this->assertSame($eventExcerpt, $result);
    }

    public function testUpdateEventExcerptThrowsExceptionForInvalidIcon(): void
    {
        // Arrange
        $eventExcerpt = $this->createMock(EventExcerpt::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'icon' => [
                        'ids' => [999],
                    ],
                ],
            ],
        ]);

        $eventExcerpt->expects($this->once())
            ->method('removeIcons');

        $this->mediaRepository
            ->expects($this->once())
            ->method('findMediaById')
            ->with(999)
            ->willReturn(null);

        $this->mediaRepository
            ->expects($this->once())
            ->method('getClassName')
            ->willReturn(Media::class);

        // Assert
        $this->expectException(EntityNotFoundException::class);

        // Act
        $this->eventExcerptModel->updateEventExcerpt($eventExcerpt, $request);
    }

    public function testUpdateEventExcerptThrowsExceptionForInvalidImage(): void
    {
        // Arrange
        $eventExcerpt = $this->createMock(EventExcerpt::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'images' => [
                        'ids' => [999],
                    ],
                ],
            ],
        ]);

        $eventExcerpt->expects($this->once())
            ->method('removeImages');

        $this->mediaRepository
            ->expects($this->once())
            ->method('findMediaById')
            ->with(999)
            ->willReturn(null);

        $this->mediaRepository
            ->expects($this->once())
            ->method('getClassName')
            ->willReturn(Media::class);

        // Assert
        $this->expectException(EntityNotFoundException::class);

        // Act
        $this->eventExcerptModel->updateEventExcerpt($eventExcerpt, $request);
    }

    public function testUpdateEventExcerptWithAllFields(): void
    {
        // Arrange
        $eventExcerpt = $this->createMock(EventExcerpt::class);
        $category = $this->createMock(CategoryInterface::class);
        $tag = $this->createMock(TagInterface::class);
        $icon = $this->createMock(Media::class);
        $image = $this->createMock(Media::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'locale' => 'de',
                    'title' => 'Full Excerpt',
                    'more' => 'Mehr lesen',
                    'description' => 'Vollständige Beschreibung',
                    'categories' => [5],
                    'tags' => ['Tag1'],
                    'icon' => ['ids' => [100]],
                    'images' => ['ids' => [200]],
                ],
            ],
        ]);

        // Basic fields
        $eventExcerpt->expects($this->once())->method('setLocale')->with('de');
        $eventExcerpt->expects($this->once())->method('setTitle')->with('Full Excerpt');
        $eventExcerpt->expects($this->once())->method('setMore')->with('Mehr lesen');
        $eventExcerpt->expects($this->once())->method('setDescription')->with('Vollständige Beschreibung');

        // Categories
        $eventExcerpt->expects($this->once())->method('removeCategories');
        $this->categoryManager->expects($this->once())
            ->method('findByIds')
            ->with([5])
            ->willReturn([$category]);
        $eventExcerpt->expects($this->once())->method('addCategory')->with($category);

        // Tags
        $eventExcerpt->expects($this->once())->method('removeTags');
        $this->tagManager->expects($this->once())
            ->method('findOrCreateByName')
            ->with('Tag1')
            ->willReturn($tag);
        $eventExcerpt->expects($this->once())->method('addTag')->with($tag);

        // Icons
        $eventExcerpt->expects($this->once())->method('removeIcons');

        // Images
        $eventExcerpt->expects($this->once())->method('removeImages');

        // Mock media repository to return different objects based on ID
        $this->mediaRepository
            ->method('findMediaById')
            ->willReturnCallback(function ($id) use ($icon, $image) {
                return match ($id) {
                    100 => $icon,
                    200 => $image,
                    default => null,
                };
            });

        $eventExcerpt->expects($this->once())->method('addIcon')->with($icon);
        $eventExcerpt->expects($this->once())->method('addImage')->with($image);

        $this->eventExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventExcerpt)
            ->willReturn($eventExcerpt);

        // Act
        $result = $this->eventExcerptModel->updateEventExcerpt($eventExcerpt, $request);

        // Assert
        $this->assertSame($eventExcerpt, $result);
    }

    public function testUpdateEventExcerptWithEmptyData(): void
    {
        // Arrange
        $eventExcerpt = $this->createMock(EventExcerpt::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [],
            ],
        ]);

        $eventExcerpt->expects($this->never())->method('setLocale');
        $eventExcerpt->expects($this->never())->method('setTitle');
        $eventExcerpt->expects($this->never())->method('setMore');
        $eventExcerpt->expects($this->never())->method('setDescription');

        $this->eventExcerptRepository
            ->expects($this->never())
            ->method('save');

        // Act
        $result = $this->eventExcerptModel->updateEventExcerpt($eventExcerpt, $request);

        // Assert
        $this->assertSame($eventExcerpt, $result);
    }
}
