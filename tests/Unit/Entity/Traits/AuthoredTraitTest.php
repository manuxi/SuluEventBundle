<?php

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity\Traits;

use DateTime;
use Manuxi\SuluEventBundle\Entity\Traits\AuthoredTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class AuthoredTraitTest extends SuluTestCase
{
    private $mock;

    protected function setUp(): void
    {
        $this->mock  = $this->getMockForTrait(AuthoredTrait::class);
    }

    public function testSetAuthored(): void
    {
        $this->assertSame($this->mock, $this->mock->setAuthored(new DateTime()));
    }

    public function testGetAuthored(): void
    {
        $this->assertNull($this->mock->getAuthored());
        $dateTime = new DateTime();
        $this->mock->setAuthored($dateTime);
        $this->assertSame($dateTime, $this->mock->getAuthored());
    }

}
