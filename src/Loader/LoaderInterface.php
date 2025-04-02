<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;

#[AutoconfigureTag]
interface LoaderInterface
{
    public function loadClassMetadata(string $className): AttributeMetadataCollection;

    public static function getDefaultPriority(): int;
}
