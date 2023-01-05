<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader\Mapping;

interface AttributeMetaDataInterface
{
    public function getFaker(): ?string;

    public function getArgs(): array;

    public function isUnique(): bool;

    public function getPrefix(): string|int|null;

    public function getValue(): string|int|array|bool|null;
}
