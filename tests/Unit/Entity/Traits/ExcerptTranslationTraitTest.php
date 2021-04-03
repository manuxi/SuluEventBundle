<?php

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity\Traits;

use Doctrine\Common\Collections\Collection;
use Manuxi\SuluEventBundle\Entity\Traits\ExcerptTranslationTrait;
use Sulu\Bundle\CategoryBundle\Entity\Category;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\TagBundle\Entity\Tag;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ExcerptTranslationTraitTest extends SuluTestCase
{
    private $mock;
    private $collection;

    protected function setUp(): void
    {
        $this->mock = $this->getMockForTrait(ExcerptTranslationTrait::class);
    }

    public function testGetId(): void
    {
        $this->assertNull($this->mock->getId());
    }

    public function testLocale(): void
    {
        $this->assertSame('en', $this->mock->getLocale());
        $this->assertSame($this->mock, $this->mock->setLocale('de'));
        $this->assertSame('de', $this->mock->getLocale());
    }

    public function testTitle(): void
    {
        $title = 'A title';
        $this->assertNull($this->mock->getTitle());
        $this->assertSame($this->mock, $this->mock->setTitle($title));
        $this->assertSame($title, $this->mock->getTitle());
        $this->assertSame($this->mock, $this->mock->setTitle(null));
        $this->assertNull($this->mock->getTitle());
    }

    public function testMore(): void
    {
        $more = 'more...';
        $this->assertNull($this->mock->getMore());
        $this->assertSame($this->mock, $this->mock->setMore($more));
        $this->assertSame($more, $this->mock->getMore());
        $this->assertSame($this->mock, $this->mock->setMore(null));
        $this->assertNull($this->mock->getMore());
    }

    public function testDescription(): void
    {
        $description = 'This is a description...';
        $this->assertNull($this->mock->getDescription());
        $this->assertSame($this->mock, $this->mock->setDescription($description));
        $this->assertSame($description, $this->mock->getDescription());
        $this->assertSame($this->mock, $this->mock->setDescription(null));
        $this->assertNull($this->mock->getDescription());
    }

    public function testCategories(): void
    {
        $this->assertNull($this->mock->getCategories());

        $categoryA = $this->prophesize(Category::class);
        $categoryA->getId()->willReturn(42);
        $categoryB = $this->prophesize(Category::class);
        $categoryB->getId()->willReturn(43);

        $this->assertSame($this->mock, $this->mock->addCategory($categoryA->reveal()));
        $this->assertSame(1, $this->mock->getCategories()->count());
        $this->assertSame($categoryA->reveal(), $this->mock->getCategories()->first());
        $this->assertSame($this->mock, $this->mock->addCategory($categoryB->reveal()));
        $this->assertSame(2, $this->mock->getCategories()->count());
        $this->assertSame($categoryB->reveal(), $this->mock->getCategories()->last());

        $this->assertInstanceOf(Collection::class, $this->mock->getCategories());

        $ids = $this->mock->getCategoryIds();
        $this->assertIsArray($ids);
        $this->assertTrue(\in_array(42, $ids));
        $this->assertTrue(\in_array(43, $ids));

        $this->assertSame($this->mock, $this->mock->removeCategory($categoryB->reveal()));
        $this->assertSame(1, $this->mock->getCategories()->count());

        $this->assertSame($this->mock, $this->mock->addCategory($categoryB->reveal()));
        $this->assertSame(2, $this->mock->getCategories()->count());
        $this->assertSame($this->mock, $this->mock->removeCategories());
        $this->assertSame(0, $this->mock->getCategories()->count());

        $this->assertTrue([] === $this->mock->getCategoryIds());
    }

    public function testTags(): void
    {
        $this->assertNull($this->mock->getTags());

        $tagA = $this->prophesize(Tag::class);
        $tagA->getName()->willReturn("TAG42");
        $tagB = $this->prophesize(Tag::class);
        $tagB->getName()->willReturn("Hasta la vista, baby!");

        $this->assertSame($this->mock, $this->mock->addTag($tagA->reveal()));
        $this->assertSame(1, $this->mock->getTags()->count());
        $this->assertSame($tagA->reveal(), $this->mock->getTags()->first());
        $this->assertSame($this->mock, $this->mock->addTag($tagB->reveal()));
        $this->assertSame(2, $this->mock->getTags()->count());
        $this->assertSame($tagB->reveal(), $this->mock->getTags()->last());

        $this->assertInstanceOf(Collection::class, $this->mock->getTags());

        $tagNames = $this->mock->getTagNames();
        $this->assertIsArray($tagNames);
        $this->assertTrue(\in_array("TAG42", $tagNames));
        $this->assertTrue(\in_array("Hasta la vista, baby!", $tagNames));

        $this->assertSame($this->mock, $this->mock->removeTag($tagB->reveal()));
        $this->assertSame(1, $this->mock->getTags()->count());

        $this->assertSame($this->mock, $this->mock->addTag($tagB->reveal()));
        $this->assertSame(2, $this->mock->getTags()->count());
        $this->assertSame($this->mock, $this->mock->removeTags());
        $this->assertSame(0, $this->mock->getTags()->count());

        $this->assertTrue([] === $this->mock->getTagNames());
    }

    public function testIcons(): void
    {
        $this->assertNull($this->mock->getIcons());

        $mediaA = $this->prophesize(Media::class);
        $mediaA->getId()->willReturn(112);
        $mediaB = $this->prophesize(Media::class);
        $mediaB->getId()->willReturn(117);

        $this->assertSame($this->mock, $this->mock->addIcon($mediaA->reveal()));
        $this->assertSame(1, $this->mock->getIcons()->count());
        $this->assertSame($mediaA->reveal(), $this->mock->getIcons()->first());
        $this->assertSame($this->mock, $this->mock->addIcon($mediaB->reveal()));
        $this->assertSame(2, $this->mock->getIcons()->count());
        $this->assertSame($mediaB->reveal(), $this->mock->getIcons()->last());

        $this->assertInstanceOf(Collection::class, $this->mock->getIcons());

        $iconIds = $this->mock->getIconIds();
        $this->assertIsArray($iconIds);
        $this->assertArrayHasKey('ids', $iconIds);
        $this->assertTrue(\in_array(112, $iconIds['ids']));
        $this->assertTrue(\in_array(117, $iconIds['ids']));

        $this->assertSame($this->mock, $this->mock->removeIcon($mediaB->reveal()));
        $this->assertSame(1, $this->mock->getIcons()->count());

        $this->assertSame($this->mock, $this->mock->addIcon($mediaB->reveal()));
        $this->assertSame(2, $this->mock->getIcons()->count());
        $this->assertSame($this->mock, $this->mock->removeIcons());
        $this->assertSame(0, $this->mock->getIcons()->count());

        $this->assertTrue([] === $this->mock->getIconIds()['ids']);
    }

    public function testImages(): void
    {
        $this->assertNull($this->mock->getImages());

        $mediaA = $this->prophesize(Media::class);
        $mediaA->getId()->willReturn(112);
        $mediaB = $this->prophesize(Media::class);
        $mediaB->getId()->willReturn(117);

        $this->assertSame($this->mock, $this->mock->addImage($mediaA->reveal()));
        $this->assertSame(1, $this->mock->getImages()->count());
        $this->assertSame($mediaA->reveal(), $this->mock->getImages()->first());
        $this->assertSame($this->mock, $this->mock->addImage($mediaB->reveal()));
        $this->assertSame(2, $this->mock->getImages()->count());
        $this->assertSame($mediaB->reveal(), $this->mock->getImages()->last());

        $this->assertInstanceOf(Collection::class, $this->mock->getImages());

        $iconIds = $this->mock->getImageIds();
        $this->assertIsArray($iconIds);
        $this->assertArrayHasKey('ids', $iconIds);
        $this->assertTrue(\in_array(112, $iconIds['ids']));
        $this->assertTrue(\in_array(117, $iconIds['ids']));

        $this->assertSame($this->mock, $this->mock->removeImage($mediaB->reveal()));
        $this->assertSame(1, $this->mock->getImages()->count());

        $this->assertSame($this->mock, $this->mock->addImage($mediaB->reveal()));
        $this->assertSame(2, $this->mock->getImages()->count());
        $this->assertSame($this->mock, $this->mock->removeImages());
        $this->assertSame(0, $this->mock->getImages()->count());

        $this->assertTrue([] === $this->mock->getImageIds()['ids']);
    }
}
