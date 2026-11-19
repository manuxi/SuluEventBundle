<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\ListBuilder;

use Manuxi\SuluEventBundle\ListBuilder\DoctrineListRepresentationFactory;
use Manuxi\SuluEventBundle\Repository\EventDimensionContentRepository;
use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactory;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineCaseFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineJoinDescriptor;
use Sulu\Component\Rest\ListBuilder\ListRestHelperInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * An event without content in the requested locale is listed as a ghost of a locale it exists in, with
 * that locale's title, type, dates, location and image instead of an empty row. Events with content in
 * the requested locale get no ghost badge.
 */
class DoctrineListRepresentationFactoryTest extends TestCase
{
    public function testEventWithoutContentInLocaleIsListedAsGhostOfItsStoredGhostLocale(): void
    {
        $list = $this->createList(
            [$this->row('a')],
            [
                $this->unlocalized('a', 'en'),
                $this->content('a', 'de', 'Deutscher Titel', 'concert'),
                $this->content('a', 'en', 'English title', 'workshop'),
            ],
        );

        $this->assertSame('en', $list[0]['ghostLocale']);
        $this->assertSame('English title', $list[0]['title']);
        $this->assertSame('Workshop', $list[0]['type']);
        $this->assertSame('Town hall', $list[0]['location']);
        $this->assertSame(['sulu-40x40' => 'thumb-7'], $list[0]['image']);
        $this->assertSame('2026-12-03', $list[0]['startDate']);
        $this->assertTrue($list[0]['publishedState']);
    }

    public function testEventMigratedWithoutGhostLocaleUsesTheFirstWebspaceLocaleItExistsIn(): void
    {
        $list = $this->createList(
            [$this->row('a')],
            [
                $this->unlocalized('a', null),
                $this->content('a', 'en', 'English title'),
                $this->content('a', 'de', 'Deutscher Titel'),
            ],
        );

        $this->assertSame('de', $list[0]['ghostLocale']);
        $this->assertSame('Deutscher Titel', $list[0]['title']);
    }

    public function testEventWithContentInLocaleIsNoGhost(): void
    {
        $list = $this->createList(
            [$this->row('a', ['title' => 'Nederlandse titel', 'ghostLocale' => 'de'])],
            [
                $this->unlocalized('a', 'de'),
                $this->content('a', 'de', 'Deutscher Titel'),
                $this->content('a', 'nl', 'Nederlandse titel'),
            ],
        );

        $this->assertNull($list[0]['ghostLocale']);
        $this->assertSame('Nederlandse titel', $list[0]['title']);
    }

    public function testGhostKeepsValuesTheListAlreadyResolved(): void
    {
        $list = $this->createList(
            [$this->row('a', ['title' => 'Title from the ghost join'])],
            [$this->unlocalized('a', 'de'), $this->content('a', 'de', 'Deutscher Titel')],
        );

        $this->assertSame('de', $list[0]['ghostLocale']);
        $this->assertSame('Title from the ghost join', $list[0]['title']);
    }

    public function testGhostFillsAdditionalColumnOfAProjectList(): void
    {
        $list = $this->createList(
            [$this->row('a', ['teaser' => null])],
            [$this->unlocalized('a', 'de'), $this->content('a', 'de', 'Deutscher Titel') + ['subtitle' => 'Untertitel']],
            ['teaser' => new DoctrineFieldDescriptor('subtitle', 'teaser', 'dimensionContent')],
        );

        $this->assertSame('Untertitel', $list[0]['teaser']);
    }

    public function testRepositoryIsAskedForTheFieldsOfTheListColumns(): void
    {
        $repository = $this->createMock(EventDimensionContentRepository::class);
        $repository->expects($this->once())
            ->method('findDraftContentsByIds')
            ->with(['a'], $this->callback(static function (array $fields): bool {
                sort($fields);

                return ['image.id', 'location.name', 'startDate', 'title', 'type', 'workflowPlace'] === $fields;
            }))
            ->willReturn([]);

        $this->createList([$this->row('a')], [], [], $repository);
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    private function row(string $id, array $values = []): array
    {
        return $values + [
            'id'             => $id,
            'title'          => null,
            'type'           => null,
            'location'       => null,
            'image'          => null,
            'startDate'      => null,
            'publishedState' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function unlocalized(string $event, ?string $ghostLocale): array
    {
        return ['event' => $event, 'locale' => null, 'ghostLocale' => $ghostLocale];
    }

    /**
     * @return array<string, mixed>
     */
    private function content(string $event, string $locale, string $title, string $type = 'default'): array
    {
        return [
            'event'         => $event,
            'locale'        => $locale,
            'ghostLocale'   => null,
            'title'         => $title,
            'type'          => $type,
            'location.name' => 'Town hall',
            'image.id'      => 7,
            'startDate'     => new \DateTimeImmutable('2026-12-03 00:00:00'),
            'workflowPlace' => 'published',
        ];
    }

    /**
     * @param array<array<string, mixed>>            $rows
     * @param array<array<string, mixed>>            $contents
     * @param array<string, DoctrineFieldDescriptor> $additionalFieldDescriptors
     *
     * @return array<array<string, mixed>>
     */
    private function createList(
        array $rows,
        array $contents,
        array $additionalFieldDescriptors = [],
        ?EventDimensionContentRepository $repository = null,
    ): array {
        $fieldDescriptorFactory = $this->createMock(FieldDescriptorFactoryInterface::class);
        $fieldDescriptorFactory->method('getFieldDescriptors')
            ->willReturn($this->eventsListFieldDescriptors() + $additionalFieldDescriptors);

        $listBuilder = $this->createMock(DoctrineListBuilder::class);
        $listBuilder->method('execute')->willReturn($rows);
        $listBuilder->method('getCurrentPage')->willReturn(1);
        $listBuilder->method('getLimit')->willReturn(10);
        $listBuilder->method('count')->willReturn(\count($rows));

        $listBuilderFactory = $this->createMock(DoctrineListBuilderFactory::class);
        $listBuilderFactory->method('create')->willReturn($listBuilder);

        $webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $webspaceManager->method('getAllLocales')->willReturn(['de', 'en', 'nl']);

        if (null === $repository) {
            /** @var EventDimensionContentRepository&MockObject $repository */
            $repository = $this->createMock(EventDimensionContentRepository::class);
            $repository->method('findDraftContentsByIds')->willReturn($contents);
        }

        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $mediaManager->method('getFormatUrls')->willReturn([7 => ['sulu-40x40' => 'thumb-7']]);

        $eventTypeSelect = $this->createMock(EventTypeSelect::class);
        $eventTypeSelect->method('getColor')->willReturn('#000000');
        $eventTypeSelect->method('getTypeName')->willReturnCallback(static fn (string $type): string => ucfirst($type));

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn (string $id): string => match ($id) {
            'sulu_event.date_format'     => 'Y-m-d',
            'sulu_event.datetime_format' => 'Y-m-d H:i',
            default                      => $id,
        });

        $factory = new DoctrineListRepresentationFactory(
            $this->createMock(RestHelperInterface::class),
            $this->createMock(ListRestHelperInterface::class),
            $listBuilderFactory,
            $fieldDescriptorFactory,
            $webspaceManager,
            $repository,
            $mediaManager,
            $eventTypeSelect,
            $translator,
            'default',
        );

        return $factory->createDoctrineListRepresentation('events', [], ['locale' => 'nl'])->getData();
    }

    /**
     * The columns of the events list that come from the dimension content, built like the list XML does.
     *
     * @return array<string, mixed>
     */
    private function eventsListFieldDescriptors(): array
    {
        $dimensionContentJoin = new DoctrineJoinDescriptor('dimensionContent', 'Event.dimensionContents');
        $ghostJoin            = new DoctrineJoinDescriptor('ghostDimensionContent', 'Event.dimensionContents');

        return [
            'id'      => new DoctrineFieldDescriptor('uuid', 'id', 'Event'),
            'version' => new DoctrineFieldDescriptor('version', 'version', 'dimensionContent'),
            'title'   => new DoctrineCaseFieldDescriptor(
                'title',
                new DoctrineDescriptor('dimensionContent', 'title', ['dimensionContent' => $dimensionContentJoin]),
                new DoctrineDescriptor('ghostDimensionContent', 'title', ['ghostDimensionContent' => $ghostJoin]),
            ),
            'type'     => new DoctrineFieldDescriptor('type', 'type', 'dimensionContent', null, ['dimensionContent' => $dimensionContentJoin]),
            'location' => new DoctrineFieldDescriptor('name', 'location', 'locationEntity', null, [
                'dimensionContent' => $dimensionContentJoin,
                'locationEntity'   => new DoctrineJoinDescriptor('locationEntity', 'dimensionContent.location'),
            ]),
            'image' => new DoctrineFieldDescriptor('id', 'image', 'Sulu\Bundle\MediaBundle\Entity\Media', null, [
                'dimensionContent'                     => $dimensionContentJoin,
                'Sulu\Bundle\MediaBundle\Entity\Media' => new DoctrineJoinDescriptor('Sulu\Bundle\MediaBundle\Entity\Media', 'dimensionContent.image'),
            ]),
            'startDate'      => new DoctrineFieldDescriptor('startDate', 'startDate', 'dimensionContent', null, ['dimensionContent' => $dimensionContentJoin]),
            'publishedState' => new DoctrineCaseFieldDescriptor(
                'publishedState',
                new DoctrineDescriptor('dimensionContent', 'workflowPlace', ['dimensionContent' => $dimensionContentJoin]),
                new DoctrineDescriptor('ghostDimensionContent', 'workflowPlace', ['ghostDimensionContent' => $ghostJoin]),
            ),
            'ghostLocale' => new DoctrineFieldDescriptor('locale', 'ghostLocale', 'ghostDimensionContent', null, ['ghostDimensionContent' => $ghostJoin]),
        ];
    }
}
