<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Import;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Import\ImageFiller;
use Manuxi\SuluEventBundle\Import\ImportSettings;
use Manuxi\SuluEventBundle\Import\ImportUserSwitcher;
use Manuxi\SuluEventBundle\Import\Resolver\MediaResolver;
use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;
use Manuxi\SuluEventBundle\Tests\Integration\Stub\StubCategory;
use PHPUnit\Framework\MockObject\MockObject;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Events without an image get the one of their first topic, never a second one.
 */
class ImageFillerTest extends AbstractEventDatabaseTestCase
{
    private FakeContentManager $contentManager;

    private ContentWorkflowInterface&MockObject $workflow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentManager = new FakeContentManager($this->entityManager);
        $this->workflow       = $this->createMock(ContentWorkflowInterface::class);

        // topics with their keys
        $this->categories['web']  = new StubCategory('web-php');
        $this->categories['ai']   = new StubCategory('ai');
        $this->categories['none'] = new StubCategory('unmapped');
        foreach (['web', 'ai', 'none'] as $key) {
            $this->entityManager->persist($this->categories[$key]);
        }
        $this->entityManager->flush();
    }

    public function testEventWithoutAnImageGetsTheImageOfItsFirstTopic(): void
    {
        $this->createEvent('a', 'de', '2027-06-01 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT, categories: ['none', 'ai']);

        $result = $this->filler()->fill();

        $this->assertSame(['Event a'], $result['events']);
        $this->assertCount(1, $this->contentManager->calls);
        // the first topic that has an image: "unmapped" has none, "ai" has
        $this->assertSame(['id' => 412], $this->contentManager->calls[0]['data']['image']);
        $this->assertSame(['image' => ['id' => 412]], $this->contentManager->calls[0]['data']['excerpt']);
        $this->assertSame('de', $this->contentManager->calls[0]['locale']);
    }

    public function testExistingImageIsNeverReplaced(): void
    {
        $this->createEvent('a', 'de', '2027-06-01 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT, categories: ['ai']);
        $row = $this->draftOf('a');
        $row->setExcerptData(['title' => 'Mine', 'image' => ['id' => 5]]);
        $row->setTemplateData(['image' => ['id' => 5]]);
        $this->entityManager->flush();

        $result = $this->filler()->fill();

        $this->assertSame([], $result['events']);
        $this->assertSame([], $this->contentManager->calls);
    }

    public function testRealImageThatIsNotInTheTemplateDataIsWrittenThereButNotChanged(): void
    {
        // older runs wrote the image as a bare id: it is in the database, the website resolves nothing (no image on the cards)
        $this->createEvent('a', 'de', '2027-06-01 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT, categories: ['ai']);
        $row = $this->draftOf('a');
        $row->setExcerptData(['image' => ['id' => 5]]);
        $this->entityManager->flush();

        $this->filler()->fill();

        $this->assertSame(['id' => 5], $this->contentManager->calls[0]['data']['image'], 'the image of the editors stays, in the form of the website');
    }

    public function testEventWithoutAMappedTopicStaysAsItIs(): void
    {
        $this->createEvent('a', 'de', '2027-06-01 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT, categories: ['none']);
        $this->createEvent('b', 'de', '2027-06-02 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT);

        $this->assertSame([], $this->filler()->fill()['events']);
    }

    public function testOtherEntriesOfTheExcerptAreCarriedAlong(): void
    {
        $this->createEvent('a', 'de', '2027-06-01 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT, categories: ['web']);
        $this->draftOf('a')->setExcerptData(['title' => 'Mine', 'description' => 'Text']);
        $this->entityManager->flush();

        $this->filler()->fill();

        $this->assertSame(['title' => 'Mine', 'description' => 'Text', 'image' => ['id' => 423]], $this->contentManager->calls[0]['data']['excerpt']);
    }

    public function testPublishedEventBecomesADraftWithTheImage(): void
    {
        $this->createEvent('a', 'de', '2027-06-01 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT, categories: ['web']);
        $this->draftOf('a')->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        $this->entityManager->flush();

        $this->workflow->expects($this->once())->method('apply')->with($this->anything(), ['locale' => 'de'], WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT);

        $this->filler()->fill();
    }

    public function testEveryLanguageGetsItsImage(): void
    {
        $this->createEvent('a', 'de', '2027-06-01 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT, categories: ['ai']);
        $this->addDraft('a', 'en', 'ai');

        $result = $this->filler()->fill();

        // one event, two drafts
        $this->assertSame(['Event a'], $result['events']);
        $this->assertSame(['de', 'en'], array_column($this->contentManager->calls, 'locale'));
    }

    public function testDryRunChangesNothing(): void
    {
        $this->createEvent('a', 'de', '2027-06-01 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT, categories: ['web']);
        $this->workflow->expects($this->never())->method('apply');

        $result = $this->filler()->fill(true);

        $this->assertSame(['Event a'], $result['events']);
        $this->assertSame([], $this->contentManager->calls);
    }

    public function testMissingFileIsOneWarning(): void
    {
        $this->createEvent('a', 'de', '2027-06-01 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT, categories: ['ai']);
        $this->createEvent('b', 'de', '2027-06-02 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT, categories: ['ai']);

        $result = $this->filler(images: ['ai' => 'event-topic-gone.jpg'])->fill();

        $this->assertSame([], $result['events']);
        $this->assertSame(['The image "event-topic-gone.jpg" does not exist.'], $result['warnings']);
    }

    public function testGeneratedCardIsNotReplacedByThePlaceholderOfATopic(): void
    {
        // the image 777 lies in a placeholder folder (a generated card): better than the image of the topic, so it stays
        $this->createEvent('a', 'de', '2027-06-01 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT, categories: ['ai']);
        $row = $this->draftOf('a');
        $row->setExcerptData(['image' => ['id' => 777]]);
        $row->setTemplateData(['image' => ['id' => 777]]);
        $this->entityManager->flush();

        $result = $this->filler(cards: ['events.cards'])->fill();

        $this->assertSame([], $result['events']);
        $this->assertSame([], $this->contentManager->calls);
    }

    /**
     * @param array<string, string>|null $images
     * @param string[]                   $cards  placeholder_collections (the media 777 lies in "events.cards")
     */
    private function filler(?array $images = null, array $cards = []): ImageFiller
    {
        $images ??= ['web-php' => 'event-topic-web-php.jpg', 'ai' => 'event-topic-ai.jpg'];
        $settings = new ImportSettings(true, 'import', false, '/events', null, 'default', 'event', $images, [], null, [], [], $cards);

        $media = $this->createStub(MediaResolver::class);
        $media->method('isInSystemCollections')->willReturnCallback(static fn (int $id, array $keys): bool => 777 === $id && \in_array('events.cards', $keys, true));
        $media->method('resolve')->willReturnCallback(static fn (int|string $reference): ?int => match ($reference) {
            'event-topic-web-php.jpg' => 423,
            'event-topic-ai.jpg'      => 412,
            default                   => null,
        });

        $user           = $this->createStub(UserInterface::class);
        $userRepository = $this->createStub(UserRepositoryInterface::class);
        $userRepository->method('findUserByIdentifier')->willReturn($user);

        $this->entityManager->clear();

        return new ImageFiller(
            $this->entityManager,
            $this->repository,
            $this->contentManager,
            $this->workflow,
            $media,
            $settings,
            new ImportUserSwitcher(new TokenStorage(), $userRepository, $settings),
        );
    }

    private function draftOf(string $uuid): EventDimensionContent
    {
        $this->entityManager->clear();
        $event = $this->repository->findByUuid($uuid);
        foreach ($event->getDimensionContents() as $row) {
            if (DimensionContentInterface::STAGE_DRAFT === $row->getStage()) {
                return $row;
            }
        }

        $this->fail('no draft');
    }

    private function addDraft(string $uuid, string $locale, string $category): void
    {
        $this->entityManager->clear();
        $event = $this->repository->findByUuid($uuid);
        $row   = new EventDimensionContent($event);
        $row->setStage(DimensionContentInterface::STAGE_DRAFT);
        $row->setLocale($locale);
        $row->setTitle('Event ' . $uuid);
        $row->setStartDate(new \DateTimeImmutable('2027-06-01 10:00:00'));
        $row->setExcerptCategories([$this->entityManager->find(StubCategory::class, $this->categories[$category]->getId())]);
        $event->addDimensionContent($row);
        $this->entityManager->flush();
    }
}
