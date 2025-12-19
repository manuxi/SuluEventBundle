<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\DataMapper;

use Manuxi\SuluEventBundle\Content\DataMapper\AutoAuthorDataMapper;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AutoAuthorDataMapperTest extends TestCase
{
    private AutoAuthorDataMapper $mapper;
    private Security $security;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->mapper = new AutoAuthorDataMapper($this->security);
    }

    public function testMapAddsAuthorIfMissing(): void
    {
        $localizedContent = $this->createMock(EventDimensionContent::class);
        $localizedContent->method('getAuthor')->willReturn(null);

        $user = $this->createMock(\Sulu\Bundle\SecurityBundle\Entity\User::class);
        $contact = $this->createMock(ContactInterface::class);
        $user->method('getContact')->willReturn($contact);
        $this->security->method('getUser')->willReturn($user);

        $localizedContent->expects($this->once())->method('setAuthor')->with($contact);
        $localizedContent->expects($this->once())->method('setAuthored');

        $this->mapper->map(
            $this->createMock(DimensionContentInterface::class),
            $localizedContent,
            []
        );
    }

    public function testMapSkipsIfAuthorPresent(): void
    {
        $localizedContent = $this->createMock(EventDimensionContent::class);
        $localizedContent->method('getAuthor')->willReturn($this->createMock(ContactInterface::class));

        $localizedContent->expects($this->never())->method('setAuthor');

        $this->mapper->map(
            $this->createMock(DimensionContentInterface::class),
            $localizedContent,
            []
        );
    }
}
