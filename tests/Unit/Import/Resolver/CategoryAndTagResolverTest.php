<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Import\Resolver\CategoryResolver;
use Manuxi\SuluEventBundle\Import\Resolver\TagResolver;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Bundle\TagBundle\Tag\TagRepositoryInterface;

class CategoryAndTagResolverTest extends TestCase
{
    public function testCategoriesAreFoundByKeyAndUnknownKeysAreReported(): void
    {
        $category   = $this->createStub(CategoryInterface::class);
        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->method('findCategoryByKey')->willReturnCallback(static fn (string $key) => 'web-php' === $key ? $category : null);

        [$categories, $unknown] = (new CategoryResolver($repository))->resolve(['web-php', ' ', 'missing', 'web-php']);

        $this->assertSame([$category], $categories);
        $this->assertSame(['missing'], $unknown);
    }

    public function testExistingTagsAreUsedAndMissingOnesAreCreated(): void
    {
        $existing = $this->createStub(TagInterface::class);
        $new      = $this->createMock(TagInterface::class);
        $new->expects($this->once())->method('setName')->with('LLM')->willReturnSelf();

        $repository = $this->createMock(TagRepositoryInterface::class);
        $repository->method('findTagByName')->willReturnCallback(static fn (string $name) => 'Symfony' === $name ? $existing : null);
        $repository->expects($this->once())->method('createNew')->willReturn($new);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist')->with($new);

        $resolver = new TagResolver($repository, $entityManager);

        $this->assertSame([$existing, $new], $resolver->resolve(['Symfony', 'LLM', ' ']));
        // the tag created a moment ago is not created a second time before the flush
        $this->assertSame([$new], $resolver->resolve(['llm']));
    }

    public function testDryRunCreatesNothing(): void
    {
        $existing   = $this->createStub(TagInterface::class);
        $repository = $this->createMock(TagRepositoryInterface::class);
        $repository->method('findTagByName')->willReturnCallback(static fn (string $name) => 'Symfony' === $name ? $existing : null);
        $repository->expects($this->never())->method('createNew');
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('persist');

        $this->assertSame([$existing], (new TagResolver($repository, $entityManager))->resolve(['Symfony', 'LLM'], false));
    }
}
