<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader\Mapping;

interface AttributeMetaDataInterface
{
    /**
     * Get faker attribute
     */
    public function getFaker(): string;

    /**
     * Set faker attribute
     */
    public function setFaker(string $faker);

    /**
     * Get arguments attributes
     *
     * @return string[]
     */
    public function getArgs(): array;

    /**
     * Add arguments attributes
     */
    public function setArgs(array $arg);

    /**
     * Get unique attribute
     */
    public function isUnique(): bool;

    /**
     * Set unique attribute
     */
    public function setUnique(bool $unique);
}
