<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Mcp\Tool;

use PHPUnit\Framework\TestCase;

/**
 * An MCP client sends objects as JSON objects. The schema of a PHP "array" parameter is a JSON array unless the parameter
 * says otherwise, so every array parameter needs its #[Schema] (the client rejected {"summary": "..."} for "content").
 */
class ToolSchemaTest extends TestCase
{
    private const MCP_TOOL = 'Mcp\\Capability\\Attribute\\McpTool';

    private const SCHEMA = 'Mcp\\Capability\\Attribute\\Schema';

    /**
     * @return iterable<string, array{class-string, string, string}>
     */
    public static function arrayParameters(): iterable
    {
        foreach (glob(__DIR__ . '/../../../../src/Mcp/Tool/*Tool.php') ?: [] as $file) {
            $class = 'Manuxi\\SuluEventBundle\\Mcp\\Tool\\' . basename($file, '.php');
            foreach ((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ([] === $method->getAttributes(self::MCP_TOOL)) {
                    continue;
                }
                foreach ($method->getParameters() as $parameter) {
                    $type = $parameter->getType();
                    if ($type instanceof \ReflectionNamedType && 'array' === $type->getName()) {
                        yield $class . '::' . $method->getName() . '($' . $parameter->getName() . ')' => [$class, $method->getName(), $parameter->getName()];
                    }
                }
            }
        }
    }

    /**
     * @dataProvider arrayParameters
     *
     * @param class-string $class
     */
    public function testEveryArrayParameterSaysWhetherItIsAnObjectOrAList(string $class, string $method, string $parameter): void
    {
        $reflection = null;
        foreach ((new \ReflectionMethod($class, $method))->getParameters() as $candidate) {
            if ($candidate->getName() === $parameter) {
                $reflection = $candidate;
            }
        }

        $attributes = $reflection?->getAttributes(self::SCHEMA) ?? [];
        $this->assertCount(1, $attributes, 'missing #[Schema(type: ...)]');
        $this->assertContains($attributes[0]->getArguments()['type'] ?? null, ['object', 'array']);
    }
}
