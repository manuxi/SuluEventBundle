<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Service\RequestParameters;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestParametersTest extends TestCase
{
    /**
     * @dataProvider provideLists
     *
     * @param array<string, mixed> $query
     * @param string[]             $expected
     */
    public function testGetList(array $query, array $expected): void
    {
        $this->assertSame($expected, RequestParameters::getList(Request::create('/', 'GET', $query), 'categories'));
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string[]}>
     */
    public static function provideLists(): iterable
    {
        yield 'missing' => [[], []];
        yield 'array' => [['categories' => ['12', '15']], ['12', '15']];
        yield 'single value' => [['categories' => '12'], ['12']];
        yield 'comma separated' => [['categories' => '12, 15,,16'], ['12', '15', '16']];
        yield 'array with comma separated entries' => [['categories' => ['12,15', ' 16 ']], ['12', '15', '16']];
        yield 'empty values are dropped' => [['categories' => ['', ' ']], []];
        yield 'nested arrays are ignored' => [['categories' => [['x'], '12']], ['12']];
    }

    /**
     * @dataProvider provideFlags
     */
    public function testGetFlag(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, RequestParameters::getFlag(Request::create('/', 'GET', ['free' => $value]), 'free'));
    }

    /**
     * @return iterable<string, array{mixed, bool}>
     */
    public static function provideFlags(): iterable
    {
        yield 'one' => ['1', true];
        yield 'true' => ['true', true];
        yield 'yes' => ['yes', true];
        yield 'on' => ['on', true];
        yield 'zero' => ['0', false];
        yield 'false' => ['false', false];
        yield 'empty' => ['', false];
        yield 'nonsense' => ['maybe', false];
        yield 'array' => [['1'], false];
    }

    public function testMissingFlagIsFalse(): void
    {
        $this->assertFalse(RequestParameters::getFlag(Request::create('/'), 'free'));
    }
}
