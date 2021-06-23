<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader;

use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;

interface LoaderInterface
{
    public function loadClassMetadata(string $className): AttributeMetadataCollection;
}
