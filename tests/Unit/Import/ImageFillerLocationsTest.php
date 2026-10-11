<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Import\ImageFiller;
use Manuxi\SuluEventBundle\Import\ImportSettings;
use Manuxi\SuluEventBundle\Import\ImportUserSwitcher;
use Manuxi\SuluEventBundle\Import\Resolver\MediaResolver;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * The places that have no image get the one of their own, the others keep theirs.
 */
class ImageFillerLocationsTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;

    private MediaResolver&MockObject $media;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->media         = $this->createMock(MediaResolver::class);
    }

    public function testPlaceWithoutAnImageGetsTheConfiguredOne(): void
    {
        $bare   = (new Location())->setName('Messe Berlin');
        $has    = (new Location())->setName('Koelnmesse');
        $photo  = $this->createMock(MediaInterface::class);
        $has->setImage($photo);
        $image  = $this->createMock(MediaInterface::class);
        $this->repository([$bare, $has]);
        $this->media->method('resolve')->with('event-venue.jpg')->willReturn(77);
        $this->entityManager->expects($this->once())->method('getReference')->with(Media::class, 77)->willReturn($image);
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->filler('event-venue.jpg')->fill();

        $this->assertSame(['Messe Berlin'], $result['locations']);
        $this->assertSame($image, $bare->getImage());
        $this->assertSame($photo, $has->getImage(), 'a photo of the editors stays');
    }

    public function testNothingIsConfiguredNothingHappens(): void
    {
        $this->repository([(new Location())->setName('Messe Berlin')]);
        $this->entityManager->expects($this->never())->method('getReference');

        $this->assertSame([], $this->filler(null)->fill()['locations']);
    }

    public function testDryRunOnlyNamesThePlaces(): void
    {
        $bare = (new Location())->setName('Messe Berlin');
        $this->repository([$bare]);
        $this->media->method('resolve')->willReturn(77);
        $this->entityManager->expects($this->never())->method('getReference');
        $this->entityManager->expects($this->never())->method('flush');

        $result = $this->filler('event-venue.jpg')->fill(true);

        $this->assertSame(['Messe Berlin'], $result['locations']);
        $this->assertNull($bare->getImage());
    }

    public function testMissingFileIsAWarning(): void
    {
        $this->repository([(new Location())->setName('Messe Berlin')]);
        $this->media->method('resolve')->willReturn(null);

        $result = $this->filler('event-venue.jpg')->fill();

        $this->assertSame([], $result['locations']);
        $this->assertSame(['The image "event-venue.jpg" does not exist.'], $result['warnings']);
    }

    /**
     * @param list<Location> $locations
     */
    private function repository(array $locations): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findAll')->willReturn($locations);
        $this->entityManager->method('getRepository')->with(Location::class)->willReturn($repository);
    }

    private function filler(int|string|null $locationImage): ImageFiller
    {
        $events = $this->createMock(EventRepository::class);
        $events->method('findAll')->willReturn([]);

        $settings       = new ImportSettings(true, 'import', false, '/events', null, 'default', 'event', [], [], $locationImage);
        $user           = $this->createStub(UserInterface::class);
        $userRepository = $this->createStub(UserRepositoryInterface::class);
        $userRepository->method('findUserByIdentifier')->willReturn($user);

        return new ImageFiller(
            $this->entityManager,
            $events,
            $this->createMock(ContentManagerInterface::class),
            $this->createMock(ContentWorkflowInterface::class),
            $this->media,
            $settings,
            new ImportUserSwitcher(new TokenStorage(), $userRepository, $settings),
        );
    }
}
