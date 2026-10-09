<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Manuxi\SuluEventBundle\Service\ImageTemplateDataCleanup;
use PHPUnit\Framework\TestCase;

class ImageTemplateDataCleanupTest extends TestCase
{
    private Connection $connection;
    private ImageTemplateDataCleanup $cleanup;

    protected function setUp(): void
    {
        if (!\extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite is required.');
        }

        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement(
            'CREATE TABLE app_event_dimension_content (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                event_uuid VARCHAR(36) NOT NULL,
                locale VARCHAR(5) NULL,
                stage VARCHAR(16) NOT NULL,
                image_id INTEGER NULL,
                templateData CLOB NULL,
                changed VARCHAR(30) NOT NULL DEFAULT \'2026-10-01 10:00:00\'
            )'
        );

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getTableName')->willReturn('app_event_dimension_content');
        $metadata->method('getColumnName')->with('templateData')->willReturn('templateData');
        $metadata->method('getSingleAssociationJoinColumnName')->willReturnMap([
            ['image', 'image_id'],
            ['event', 'event_uuid'],
        ]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($this->connection);
        $entityManager->method('getClassMetadata')->willReturn($metadata);

        $this->cleanup = new ImageTemplateDataCleanup($entityManager);
    }

    public function testReducesTheMediaObjectToItsIdAndKeepsTheRestOfTheData(): void
    {
        $id = $this->insert('draft', 'de', 241, [
            'title' => 'Title',
            'image' => self::fullMediaObject(241),
            'images' => ['ids' => [1, 2]],
            'details' => [['type' => 'text', 'text' => '<p>Ä "quoted" / slash</p>']],
        ]);

        $result = $this->cleanup->run(false);

        $this->assertSame(1, $result['scanned']);
        $this->assertCount(1, $result['entries']);
        $this->assertSame([], $result['failures']);
        $this->assertSame($id, $result['entries'][0]['id']);
        $this->assertSame('{"id":241}', $result['entries'][0]['after']);
        $this->assertNull($result['entries'][0]['note']);

        $this->assertSame(
            [
                'title' => 'Title',
                'image' => ['id' => 241],
                'images' => ['ids' => [1, 2]],
                'details' => [['type' => 'text', 'text' => '<p>Ä "quoted" / slash</p>']],
            ],
            $this->templateData($id),
        );
    }

    public function testDryRunReportsButDoesNotWrite(): void
    {
        $id = $this->insert('live', 'de', 241, ['image' => self::fullMediaObject(241)]);
        $before = $this->rawTemplateData($id);

        $result = $this->cleanup->run(true);

        $this->assertCount(1, $result['entries']);
        $this->assertSame($before, $this->rawTemplateData($id));
    }

    public function testRowsWithoutAnImageObjectAreLeftAlone(): void
    {
        $withId = $this->insert('draft', 'de', 241, ['image' => ['id' => 241]]);
        $without = $this->insert('draft', 'en', null, ['title' => 'No image']);
        $unlocalized = $this->insert('draft', null, 241, []);
        $empty = $this->insertRaw('live', 'de', null, null);

        $rawWithId = $this->rawTemplateData($withId);

        $result = $this->cleanup->run(false);

        $this->assertSame(4, $result['scanned']);
        $this->assertSame([], $result['entries']);
        $this->assertSame([], $result['failures']);
        $this->assertSame($rawWithId, $this->rawTemplateData($withId));
        $this->assertSame(['title' => 'No image'], $this->templateData($without));
        $this->assertSame([], $this->templateData($unlocalized));
        $this->assertNull($this->rawTemplateData($empty));
    }

    public function testTheColumnIsTheSourceOfTheImageId(): void
    {
        $columnWins = $this->insert('draft', 'de', 5, ['image' => self::fullMediaObject(6)]);
        $objectIsUsed = $this->insert('draft', 'en', null, ['image' => self::fullMediaObject(238)]);

        $result = $this->cleanup->run(false);

        $this->assertCount(2, $result['entries']);
        $this->assertSame(['id' => 5], $this->templateData($columnWins)['image']);
        $this->assertSame(['id' => 238], $this->templateData($objectIsUsed)['image']);
        $this->assertStringContainsString('column kept', (string) $result['entries'][0]['note']);
        $this->assertNull($result['entries'][1]['note']);
    }

    public function testAnObjectWithoutAnyIdIsRemoved(): void
    {
        $id = $this->insert('draft', 'de', null, ['title' => 'T', 'image' => ['url' => '/x.jpg', 'thumbnails' => []]]);

        $result = $this->cleanup->run(false);

        $this->assertSame('removed', $result['entries'][0]['after']);
        $this->assertSame(['title' => 'T'], $this->templateData($id));
    }

    public function testABrokenRowIsReportedAndTheOthersAreStillCleaned(): void
    {
        $broken = $this->insertRaw('draft', 'de', 1, '{not json');
        $good = $this->insert('draft', 'en', 241, ['image' => self::fullMediaObject(241)]);

        $result = $this->cleanup->run(false);

        $this->assertCount(1, $result['failures']);
        $this->assertSame($broken, $result['failures'][0]['id']);
        $this->assertSame('draft', $result['failures'][0]['stage']);
        $this->assertCount(1, $result['entries']);
        $this->assertSame(['id' => 241], $this->templateData($good)['image']);
    }

    public function testASecondRunHasNothingLeftToDo(): void
    {
        $this->insert('draft', 'de', 241, ['image' => self::fullMediaObject(241)]);
        $this->insert('live', 'de', 241, ['image' => self::fullMediaObject(241)]);

        $this->assertCount(2, $this->cleanup->run(false)['entries']);
        $this->assertSame([], $this->cleanup->run(false)['entries']);
    }

    public function testOnlyTheTemplateDataColumnIsWritten(): void
    {
        $id = $this->insert('draft', 'de', 241, ['image' => self::fullMediaObject(241)]);

        $this->cleanup->run(false);

        $this->assertSame(
            '2026-10-01 10:00:00',
            $this->connection->fetchOne('SELECT changed FROM app_event_dimension_content WHERE id = ?', [$id])
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function fullMediaObject(int $id): array
    {
        return [
            'id' => $id,
            'url' => '/uploads/media/original/00/' . $id . '-image.jpg?v=1-0',
            'thumbnails' => ['sulu-400x400' => '/uploads/media/sulu-400x400/00/' . $id . '-image.jpg?v=1-0'],
            'mimeType' => 'image/jpeg',
        ];
    }

    /**
     * @param array<string, mixed> $templateData
     */
    private function insert(string $stage, ?string $locale, ?int $imageId, array $templateData): int
    {
        return $this->insertRaw($stage, $locale, $imageId, \json_encode($templateData, JSON_THROW_ON_ERROR));
    }

    private function insertRaw(string $stage, ?string $locale, ?int $imageId, ?string $templateData): int
    {
        $this->connection->insert('app_event_dimension_content', [
            'event_uuid' => '01a0e097-0000-7000-8000-000000000000',
            'locale' => $locale,
            'stage' => $stage,
            'image_id' => $imageId,
            'templateData' => $templateData,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function rawTemplateData(int $id): ?string
    {
        $value = $this->connection->fetchOne('SELECT templateData FROM app_event_dimension_content WHERE id = ?', [$id]);

        return false === $value ? null : $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function templateData(int $id): array
    {
        return \json_decode((string) $this->rawTemplateData($id), true, 512, JSON_THROW_ON_ERROR);
    }
}
