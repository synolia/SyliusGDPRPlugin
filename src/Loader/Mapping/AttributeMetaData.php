<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader\Mapping;

final class AttributeMetaData implements AttributeMetaDataInterface
{
    /** @var string */
    private $faker;

    /** @var array */
    private $args = [];

    /** @var bool */
    private $unique = false;

    public function __construct(string $faker = '', array $args = [], bool $unique = false)
    {
        $this->setFaker($faker)
            ->setArgs($args)
            ->setUnique($unique)
        ;
    }

    /**
     * Get faker attribute
     */
    public function getFaker(): string
    {
        return $this->faker;
    }

    /**
     * Set faker attribute
     *
     * @return self
     */
    public function setFaker(string $faker)
    {
        $this->faker = $faker;

        return $this;
    }

    /**
     * Get arguments attributes
     *
     * @return string[]
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Add arguments attributes
     *
     * @return self
     */
    public function setArgs(array $args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Get unique attribute
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * Set unique attribute
     *
     * @return self
     */
    public function setUnique(bool $unique)
    {
        $this->unique = $unique;

        return $this;
    }
}
