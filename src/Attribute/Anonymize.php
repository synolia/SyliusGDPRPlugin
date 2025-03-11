<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Attribute;

use Doctrine\ORM\Mapping\MappingAttribute;
use Synolia\SyliusGDPRPlugin\Validator\FakerOptionsValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Anonymize implements MappingAttribute
{
    public readonly ?string $faker;

    public readonly array $args;

    public readonly bool $unique;

    public readonly string|int|null $prefix;

    public readonly string|int|array|bool|null $value;

    public function __construct(array $options = [])
    {
        $anonymize = new FakerOptionsValidator($options);
        $this->faker = $anonymize->faker;
        $this->args = $anonymize->args;
        $this->unique = $anonymize->unique;
        $this->prefix = $anonymize->prefix;
        $this->value = $anonymize->value;
    }
}
