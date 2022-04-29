<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader\Mapping;

interface AttributeMetaDataInterface
{
    public function getFaker(): ?string;

    public function setFaker(?string $faker): self;

    public function getArgs(): array;

    public function setArgs(array $arg): self;

    public function isUnique(): bool;

    public function setUnique(bool $unique): self;

    /** @return int|string|null */
    public function getPrefix();

    /** @param int|string|null $prefix */
    public function setPrefix($prefix): self;

    /** @return mixed */
    public function getValue();

    /** @param mixed $value */
    public function setValue($value): self;
}
