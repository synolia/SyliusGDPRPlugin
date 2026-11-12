<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Provider;

use PHPUnit\Framework\TestCase;
use Synolia\SyliusGDPRPlugin\Provider\Anonymizer;
use Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\TypedFoo;

final class AnonymizerTypeResolutionTest extends TestCase
{
    /**
     * @dataProvider propertyTypesProvider
     */
    public function testGetBuiltinType(string $property, string $expectedType): void
    {
        $reflection = new \ReflectionClass(Anonymizer::class);
        $anonymizer = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('getBuiltinType');

        $this->assertSame($expectedType, $method->invoke($anonymizer, TypedFoo::class, $property));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function propertyTypesProvider(): iterable
    {
        yield 'untyped' => ['untyped', 'string'];
        yield 'nullable int' => ['nullableInt', 'int'];
        yield 'union' => ['union', 'int'];
        yield 'nullable union' => ['nullableUnion', 'int'];
        yield 'object or string union' => ['objectOrString', 'object'];
        yield 'numeric union' => ['number', 'float'];
        yield 'object' => ['date', 'object'];
        yield 'nullable object' => ['nullableDate', 'object'];
        yield 'bool' => ['bool', 'bool'];
        yield 'float' => ['float', 'float'];
        yield 'array' => ['array', 'array'];
        yield 'docblock array' => ['docBlockArray', 'array'];
        yield 'nullable string' => ['nullableString', 'string'];
    }
}
