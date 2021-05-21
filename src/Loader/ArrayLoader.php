<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader;

use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetaData;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;

final class ArrayLoader implements LoaderInterface
{
    /**
     * @var array
     */
    private $mappings;

    public function __construct(array $mappings = [])
    {
        $this->mappings = $mappings;
    }

    public function loadClassMetadata(string $className): AttributeMetadataCollection
    {
        $attributeMetaDataCollection = new AttributeMetadataCollection();

        foreach ($this->mappings as $mapping) {
            if (!isset($mapping[$className])) {
                continue;
            }
            $attributeMetaDataCollection = $this->assignAttributeMetaDataCollection(
                $mapping,
                $className,
                $attributeMetaDataCollection
            );
        }

        return $attributeMetaDataCollection;
    }

    private function assignAttributeMetaDataCollection(
        array $property,
        string $className,
        AttributeMetadataCollection $attributeMetaDataCollection
    ): AttributeMetadataCollection {
        foreach ($property[$className]['properties'] as $property => $options) {
            $fakerArguments = $options['args'] ?? [];
            $isUnique = $options['unique'] ?? false;
            $attributeMetaData = new AttributeMetaData($options['faker'], $fakerArguments, $isUnique);

            $attributeMetaDataCollection->add($property, $attributeMetaData);
        }

        return $attributeMetaDataCollection;
    }
}
