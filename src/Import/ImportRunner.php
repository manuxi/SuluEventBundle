<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\EventImportRun;
use Manuxi\SuluEventBundle\Repository\EventImportRunRepository;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Runs the import of all sources from the admin (the button "Import now" of the settings): the same work as the command
 * sulu:event:import, followed by the images that are still missing (sulu:event:fill-images). The result is stored as a run
 * like the one of the command, so the tab "Import" shows it.
 *
 * Only one run at a time: while one is going, the flag in the cache says so (it expires after STALE_AFTER seconds, so a run
 * that died does not block the button for good).
 */
class ImportRunner
{
    public const FLAG_KEY = 'sulu_event_import_running';

    /** the progress of the run that is going or of the last one (state, done, known, source, ...); it stays for an hour after the end */
    public const PROGRESS_KEY = 'sulu_event_import_progress';

    public const PROGRESS_KEEP = 3600;

    /** a run longer than this is taken for dead */
    public const STALE_AFTER = 1800;

    public function __construct(
        private readonly EventImporter $importer,
        private readonly ImageFiller $imageFiller,
        private readonly SourceRegistry $sources,
        private readonly ImportSettings $settings,
        private readonly EventImportRunRepository $runRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * Whether the import can run at all: switched on and at least one source.
     */
    public function isAvailable(): bool
    {
        return $this->settings->enabled && [] !== $this->sources->all();
    }

    /**
     * @return int|null the time (unix) the running import started, null if none is running
     */
    public function runningSince(): ?int
    {
        $item = $this->cache->getItem(self::FLAG_KEY);

        return $item->isHit() ? (int) $item->get() : null;
    }

    /**
     * What the run that is going (or ended less than an hour ago) has done so far.
     *
     * @return array{state: string, done: int, known: int, source: string|null, sourceIndex: int, sourceCount: int, at: int}|null state is "running" or "done"
     */
    public function progress(): ?array
    {
        $item = $this->cache->getItem(self::PROGRESS_KEY);

        return $item->isHit() ? $item->get() : null;
    }

    /**
     * Marks the import as running. False if one is running already or the import cannot run.
     */
    public function reserve(): bool
    {
        if (!$this->isAvailable() || null !== $this->runningSince()) {
            return false;
        }

        $item = $this->cache->getItem(self::FLAG_KEY);
        $item->set(time())->expiresAfter(self::STALE_AFTER);
        $this->cache->save($item);
        $this->saveProgress('running', 0, 0, null, 0, 0);

        return true;
    }

    private function saveProgress(string $state, int $done, int $known, ?string $source, int $sourceIndex, int $sourceCount): void
    {
        $item = $this->cache->getItem(self::PROGRESS_KEY);
        $item->set(['state' => $state, 'done' => $done, 'known' => $known, 'source' => $source, 'sourceIndex' => $sourceIndex, 'sourceCount' => $sourceCount, 'at' => time()]);
        $item->expiresAfter('running' === $state ? self::STALE_AFTER : self::PROGRESS_KEEP);
        $this->cache->save($item);
    }

    /**
     * Does the work of a run that was reserved, and frees the flag at the end.
     */
    public function run(): ?EventImportRun
    {
        ignore_user_abort(true);
        if (\function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $startedAt = new \DateTimeImmutable();
        $run       = new EventImportRun($startedAt, null, false);

        $done  = 0;
        $known = 0;
        $last  = 0;
        $tick  = function (string $source, int $index, int $count, int $processed, int $total) use (&$done, &$known, &$last): void {
            $done  = $processed;
            $known = $total;
            // the cache is written every few candidates, not every one
            if (0 === $processed % 5 || $processed === $total || time() !== $last) {
                $last = time();
                $this->saveProgress('running', $processed, $total, $source, $index, $count);
            }
        };

        try {
            try {
                $report = $this->importer->import(null, false, null, $tick);
                $run->finish(new \DateTimeImmutable(), $report->getCounts(), $report->toArray());
                $this->imageFiller->fill();
            } catch (\Throwable $e) {
                // a failed run is a run as well: the tab shows the error instead of the result of the run before
                $run->finish(
                    new \DateTimeImmutable(),
                    ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'locked' => 0, 'vanished' => 0, 'rejected' => 0, 'filtered' => 0, 'errors' => 1, 'newLocations' => 0],
                    ['entries' => [['action' => ImportReport::ACTION_ERROR, 'source' => 'import', 'title' => null, 'externalId' => null, 'detail' => $e->getMessage(), 'lockedFields' => []]], 'newLocations' => [], 'warnings' => []]
                );
            }

            $this->store($run);
            $this->saveProgress('done', $done, max($known, $done), null, 0, 0);

            return $run;
        } finally {
            $this->cache->deleteItem(self::FLAG_KEY);
        }
    }

    private function store(EventImportRun $run): void
    {
        // the entity manager may have been cleared by the import, so the run is added to the current one
        $this->runRepository->add($run);
        $this->entityManager->flush();
        $this->runRepository->prune();
    }
}
