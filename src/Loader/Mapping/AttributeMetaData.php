<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader\Mapping;

use Synolia\SyliusGDPRPlugin\Validator\FakerOptionsValidator;

final readonly class AttributeMetaData implements AttributeMetaDataInterface
{
    public function __construct(
        private ?string $faker = null,
        private array $args = [],
        private bool $unique = false,
        private string|int|null $prefix = '',
        private string|int|array|bool|null $value = FakerOptionsValidator::DEFAULT_VALUE,
    ) {
    }

    public function getFaker(): ?string
    {
        return $this->faker;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function getPrefix(): string|int|null
    {
        return $this->prefix;
    }

    public function getValue(): string|int|array|bool|null
    {
        return $this->value;
    }
}
