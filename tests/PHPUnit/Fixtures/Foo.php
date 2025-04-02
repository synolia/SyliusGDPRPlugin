<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures;

use Synolia\SyliusGDPRPlugin\Attribute\Anonymize;

class Foo
{
    #[Anonymize(['faker' => 'email'])]
    public $email = '';

    #[Anonymize(['value' => 'test-annonation-value'])]
    public $value;

    #[Anonymize(['value' => 'test-annonation-value-without-property'])]
    public $valueWithoutProperty;

    #[Anonymize(['faker' => 'email', 'prefix' => 'test-annotation-prefix-'])]
    public $prefix;

    #[Anonymize(['value' => 'value', 'prefix' => 'test-annotation-prefix-value-'])]
    public $prefixValue;

    #[Anonymize(['value' => 'attribute'])]
    public $mergeYamlAnnotationConfiguration;

    #[Anonymize(['value' => ['value1', 'value2']])]
    public array $arrayValue;

    #[Anonymize(['value' => '1542', 'prefix' => '1542'])]
    public $integer;

    public $bar;
}
