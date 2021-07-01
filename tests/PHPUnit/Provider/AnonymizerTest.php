<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Provider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Synolia\SyliusGDPRPlugin\Provider\Anonymizer;
use Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\Foo;
use Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\YamlFoo;

final class AnonymizerTest extends KernelTestCase
{
    /** @var Anonymizer */
    private $anonymizer;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->anonymizer = self::$container->get(Anonymizer::class);
    }

    public function testPassArrayToAnonymizerThrowAnException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This is not an object');

        $data = ['coucou' => 'hello'];
        $this->anonymizer->anonymize($data, false, 10000);
    }

    public function testPassStringToAnonymizerThrowAnException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This is not an object');

        $this->anonymizer->anonymize('coucou', false, 10000);
    }

    public function testPassIntToAnonymizerThrowAnException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This is not an object');

        $this->anonymizer->anonymize(12, false, 10000);
    }

    public function testAnonymizeWithAnnotationAndYamlProperties(): void
    {
        $foo = new Foo();
        $foo->email = 'contact@synolia.com';
        $foo->bar = 'coucou';

        $this->anonymizer->anonymize($foo, false, 10000);

        $this->assertSame($foo->bar, 'coucou');
        $this->assertSame('test-annonation-value', $foo->value);
        $this->assertSame('test-annonation-value-without-property', $foo->valueWithoutProperty);
        $this->assertStringContainsString('test-annotation-prefix-', $foo->prefix);
        $this->assertStringContainsString('@', $foo->prefix);
        $this->assertSame('test-annotation-prefix-value-value', $foo->prefixValue);
        $this->assertNotSame('contact@synolia.com', $foo->email);
        $this->assertNotSame('annotation', $foo->mergeYamlAnnotationConfiguration);
        $this->assertStringContainsString('@', $foo->mergeYamlAnnotationConfiguration);
        $this->assertStringContainsString('@', $foo->email);
        $this->assertIsInt($foo->integer);
        $this->assertIsArray($foo->arrayValue);
        $this->assertCount(2, $foo->arrayValue);
        $this->assertSame(15421542, $foo->integer);
    }

    public function testYamlConfig(): void
    {
        $foo = new YamlFoo();
        $foo->email = 'contact@synolia.com';
        $foo->bar = 'coucou';

        $this->anonymizer->anonymize($foo, false, 10000);

        $this->assertStringContainsString('@', $foo->bar);
        $this->assertSame('test-yaml-value', $foo->value);
        $this->assertStringContainsString('test-yaml-prefix-', $foo->prefix);
        $this->assertStringContainsString('@', $foo->prefix);
        $this->assertSame('test-yaml-prefix-value', $foo->prefixValue);
        $this->assertSame('anonymize@synolia.com', $foo->email);
        $this->assertStringContainsString('@', $foo->email);
        $this->assertNull($foo->nullValue);
    }
}
