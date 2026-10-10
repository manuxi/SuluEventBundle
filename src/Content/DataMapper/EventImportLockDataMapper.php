<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\DataMapper;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;
use Sulu\Content\Application\ContentDataMapper\DataMapper\DataMapperInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

/**
 * The "Source" tab can release the locks the import set on fields that were changed in the admin: with
 * "importUnlockAll" the locked fields are forgotten, so the next import writes the values of the source again. The
 * flag is not stored, it is only a command.
 */
class EventImportLockDataMapper implements DataMapperInterface
{
    public function __construct(private readonly EventImportRepository $importRepository)
    {
    }

    public function map(
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent,
        array $data,
    ): void {
        if (!$localizedDimensionContent instanceof EventDimensionContent) {
            return;
        }

        if (!isset($data['importUnlockAll']) || true !== filter_var($data['importUnlockAll'], \FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        foreach ($this->importRepository->findByEventUuid($localizedDimensionContent->getEvent()->getUuid()) as $entry) {
            $locked = $entry->getLockedFields();
            if ([] === $locked) {
                continue;
            }

            // without the remembered value the next run does not take the field for a change made by someone else
            $values = $entry->getImportedValues();
            foreach ($locked as $field) {
                unset($values[$field]);
            }

            $entry->setImportedValues($values)->setLockedFields([])->setContentHash(null);
        }
    }
}
