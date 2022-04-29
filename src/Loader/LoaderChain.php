<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader;

use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;

final class LoaderChain implements LoaderInterface
{
    /** @var LoaderInterface[] */
    private array $loaders = [];

    public function addLoader(LoaderInterface $loader): void
    {
        if ($loader instanceof self) {
            return;
        }

        $this->loaders[] = $loader;
    }

    public function loadClassMetadata(string $className): AttributeMetadataCollection
    {
        $fullCollect = new AttributeMetadataCollection();
        foreach ($this->loaders as $loader) {
            $collection = $loader->loadClassMetadata($className);

            foreach ($collection->get() as $property => $attributeMetadata) {
                $fullCollect->add($property, $attributeMetadata);
            }
        }

        return $fullCollect;
    }
}
