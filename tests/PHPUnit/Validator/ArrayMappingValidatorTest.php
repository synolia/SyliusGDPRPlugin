<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Validator;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Synolia\SyliusGDPRPlugin\Exception\GDPRPropertyException;
use Synolia\SyliusGDPRPlugin\Validator\ArrayMappingValidator;

class ArrayMappingValidatorTest extends KernelTestCase
{
    private \Synolia\SyliusGDPRPlugin\Validator\ArrayMappingValidator $arrayMappingValidator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->arrayMappingValidator = new ArrayMappingValidator();
    }

    public function testCheckParseWithNoneClassName()
    {
        $this->expectException(\LogicException::class);
        $mapping = ['properties' => ['email' => ['faker' => 'email', 'args' => []]]];
        $this->arrayMappingValidator->checkParse($mapping, 'App\Test\Test');
    }

    public function testCheckParseWithWrongPropertiesKey()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('properddddddties is not supported, try properties instead.');
        $mapping = ['properddddddties' => ['email' => ['faker' => 'email', 'args' => []]]];
        $this->arrayMappingValidator->checkParse($mapping, \Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\Foo::class);
    }

    public function testCheckParseWithWrongProperty()
    {
        $this->expectException(GDPRPropertyException::class);
        $this->expectExceptionMessage('The property title does not exist in entity Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\Foo.');
        $mapping = ['properties' => ['title' => ['faker' => 'company']]];
        $this->arrayMappingValidator->checkParse($mapping, \Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\Foo::class);
    }

    public function testCheckParseWithWrongOptions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The option "fallllker" does not exist. Defined options are: "args", "faker", "prefix", "unique", "value".');
        $mapping = ['properties' => ['email' => ['fallllker' => 'company', 'args' => []]]];
        $this->arrayMappingValidator->checkParse($mapping, \Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\Foo::class);
    }
}
