<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;

/**
 * Reduces templateData.image of all event rows (draft and live, every locale) to {"id": n}.
 *
 * Older versions stored the media object of the form (url, thumbnails, mimeType) there on every save, next to the
 * image_id column that is the actual source of the image. The column is taken as the image id; the id inside the
 * object is only used when the column is empty.
 *
 * Only the templateData column is written, nothing else of the row (neither changed nor the audit fields), because
 * the content itself does not change.
 *
 * @phpstan-type Entry array{id: int, event: string, locale: string|null, stage: string, before: string, after: string, note: string|null}
 * @phpstan-type Failure array{id: int, event: string, locale: string|null, stage: string, error: string}
 * @phpstan-type Result array{scanned: int, entries: list<Entry>, failures: list<Failure>}
 */
class ImageTemplateDataCleanup
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return Result
     */
    public function run(bool $dryRun): array
    {
        $connection = $this->entityManager->getConnection();
        $metadata = $this->entityManager->getClassMetadata(EventDimensionContent::class);

        $table = $connection->quoteIdentifier($metadata->getTableName());
        $templateDataColumn = $metadata->getColumnName('templateData');
        $imageColumn = $metadata->getSingleAssociationJoinColumnName('image');
        $eventColumn = $metadata->getSingleAssociationJoinColumnName('event');

        $rows = $connection->executeQuery(\sprintf(
            'SELECT id, %s AS event, locale, stage, %s AS image_id, %s AS template_data FROM %s ORDER BY id',
            $connection->quoteIdentifier($eventColumn),
            $connection->quoteIdentifier($imageColumn),
            $connection->quoteIdentifier($templateDataColumn),
            $table,
        ))->iterateAssociative();

        $scanned = 0;
        $changes = [];
        $failures = [];
        foreach ($rows as $row) {
            ++$scanned;

            try {
                $change = $this->reduce($row);
            } catch (\Throwable $e) {
                $failures[] = $this->failure($row, $e->getMessage());

                continue;
            }

            if (null !== $change) {
                $changes[] = $change;
            }
        }

        $entries = [];
        foreach ($changes as $change) {
            if (!$dryRun) {
                try {
                    $this->write($connection, $table, $templateDataColumn, $change['row']['id'], $change['templateData']);
                } catch (\Throwable $e) {
                    $failures[] = $this->failure($change['row'], $e->getMessage());

                    continue;
                }
            }

            $entries[] = [
                'id' => (int) $change['row']['id'],
                'event' => (string) $change['row']['event'],
                'locale' => $change['row']['locale'],
                'stage' => (string) $change['row']['stage'],
                'before' => $change['before'],
                'after' => $change['after'],
                'note' => $change['note'],
            ];
        }

        return ['scanned' => $scanned, 'entries' => $entries, 'failures' => $failures];
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array{row: array<string, mixed>, templateData: array<string, mixed>, before: string, after: string, note: string|null}|null
     */
    private function reduce(array $row): ?array
    {
        if (!\is_string($row['template_data']) || '' === $row['template_data']) {
            return null;
        }

        $templateData = \json_decode($row['template_data'], true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($templateData) || !\is_array($templateData['image'] ?? null)) {
            return null;
        }

        $image = $templateData['image'];
        if (['id'] === \array_keys($image)) {
            return null;
        }

        $columnId = null !== $row['image_id'] ? (int) $row['image_id'] : null;
        $objectId = isset($image['id']) && \is_numeric($image['id']) ? (int) $image['id'] : null;
        $imageId = $columnId ?? $objectId;

        if (null === $imageId) {
            // nothing to keep: the column is empty and the object carries no id
            unset($templateData['image']);

            return [
                'row' => $row,
                'templateData' => $templateData,
                'before' => $this->describe($image),
                'after' => 'removed',
                'note' => 'no image id in the column or in the object',
            ];
        }

        $templateData['image'] = ['id' => $imageId];

        return [
            'row' => $row,
            'templateData' => $templateData,
            'before' => $this->describe($image),
            'after' => \json_encode(['id' => $imageId], JSON_THROW_ON_ERROR),
            'note' => null !== $columnId && null !== $objectId && $columnId !== $objectId
                ? \sprintf('object had id %d, image_id column is %d (column kept)', $objectId, $columnId)
                : null,
        ];
    }

    /**
     * @param array<string, mixed> $templateData
     */
    private function write(Connection $connection, string $table, string $column, mixed $id, array $templateData): void
    {
        $connection->update($table, [$column => $templateData], ['id' => $id], [$column => Types::JSON]);
    }

    /**
     * @param array<string, mixed> $image
     */
    private function describe(array $image): string
    {
        $keys = \implode(', ', \array_keys($image));

        return \sprintf('{%s}', $keys);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array{id: int, event: string, locale: string|null, stage: string, error: string}
     */
    private function failure(array $row, string $error): array
    {
        return [
            'id' => (int) $row['id'],
            'event' => (string) $row['event'],
            'locale' => $row['locale'],
            'stage' => (string) $row['stage'],
            'error' => $error,
        ];
    }
}
