<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration;

use Doctrine\ORM\Tools\SchemaValidator;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventImport;

class MappingTest extends AbstractEventDatabaseTestCase
{
    public function testEventAndItsImportsPointAtEachOther(): void
    {
        $validator = new SchemaValidator($this->entityManager);
        $factory   = $this->entityManager->getMetadataFactory();

        self::assertSame([], $validator->validateClass($factory->getMetadataFor(Event::class)));
        self::assertSame([], $validator->validateClass($factory->getMetadataFor(EventImport::class)));
    }
}
