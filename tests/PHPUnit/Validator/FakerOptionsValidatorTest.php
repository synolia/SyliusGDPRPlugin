<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Validator;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Synolia\SyliusGDPRPlugin\Validator\FakerOptionsValidator;

final class FakerOptionsValidatorTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testFakerConfirmeOptionWithUnique(): void
    {
        $options = ['faker' => 'realText', 'args' => [], 'unique' => true];
        $fakerOption = new FakerOptionsValidator($options);
        $this->assertSame(
            ['faker' => $fakerOption->faker, 'args' => $fakerOption->args, 'unique' => $fakerOption->unique],
            $options
        );
    }

    public function testFakerConfirmeOptionWithWrongFakerPropertyName(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        $options = ['fiker' => 'realText', 'args' => []];
        new FakerOptionsValidator($options);
    }

    public function testFakerConfirmeOptionWithWrongArgsPropertyName(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        $options = ['faker' => 'realText', 'orgs' => []];
        new FakerOptionsValidator($options);
    }

    public function testFakerConfirmeOptionWithWrongUniquePropertyName(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        $options = ['faker' => 'realText', 'args' => [], 'unirque' => true];
        new FakerOptionsValidator($options);
    }

    public function testFakerConfirmeOptionWithoutUnique(): void
    {
        $options = ['faker' => 'realText', 'args' => []];
        $exepctedResult = ['faker' => 'realText', 'args' => [], 'unique' => false];
        $fakerOption = new FakerOptionsValidator($options);
        $this->assertSame(
            get_object_vars($fakerOption),
            $exepctedResult
        );
    }
}
