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

    public function testPassAnnotatedObjectAnonymizeOnlyAnnotatedProperties(): void
    {
        $foo = new Foo();
        $foo->email = 'contact@synolia.com';
        $foo->bar = 'coucou';

        $this->anonymizer->anonymize($foo, false, 10000);

        $this->assertSame($foo->bar, 'coucou');
        $this->assertNotSame($foo->email, 'contact@synolia.com');
        $this->assertStringContainsString('@', $foo->email);
    }

    public function testYamlConfig(): void
    {
        $foo = new YamlFoo();
        $this->anonymizer->anonymize($foo, false, 10000);
        $this->assertStringContainsString('@', $foo->foo);
        $this->assertStringContainsString('@', $foo->bar);
    }
}
