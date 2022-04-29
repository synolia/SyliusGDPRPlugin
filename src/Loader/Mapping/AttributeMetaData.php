<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader\Mapping;

use Synolia\SyliusGDPRPlugin\Validator\FakerOptionsValidator;

final class AttributeMetaData implements AttributeMetaDataInterface
{
    private ?string $faker;

    private array $args = [];

    private bool $unique = false;

    /** @var string|int|null */
    private $prefix = '';

    /** @var string|int|array|bool|null */
    private $value = FakerOptionsValidator::DEFAULT_VALUE;

    /**
     * @param string|int|null $prefix
     * @param string|int|array|bool|null $value
     */
    public function __construct(?string $faker = null, array $args = [], bool $unique = false, $prefix = '', $value = FakerOptionsValidator::DEFAULT_VALUE)
    {
        $this->setFaker($faker)
            ->setArgs($args)
            ->setUnique($unique)
            ->setValue($value)
            ->setPrefix($prefix)
        ;
    }

    public function getFaker(): ?string
    {
        return $this->faker;
    }

    public function setFaker(?string $faker): AttributeMetaDataInterface
    {
        $this->faker = $faker;

        return $this;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function setArgs(array $args): AttributeMetaDataInterface
    {
        $this->args = $args;

        return $this;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function setUnique(bool $unique): AttributeMetaDataInterface
    {
        $this->unique = $unique;

        return $this;
    }

    /** @return int|string|null */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /** @param int|string|null $prefix */
    public function setPrefix($prefix): AttributeMetaDataInterface
    {
        $this->prefix = $prefix;

        return $this;
    }

    /** @return string|int|array|bool|null */
    public function getValue()
    {
        return $this->value;
    }

    /** @param string|int|array|bool|null $value */
    public function setValue($value): AttributeMetaDataInterface
    {
        $this->value = $value;

        return $this;
    }
}
