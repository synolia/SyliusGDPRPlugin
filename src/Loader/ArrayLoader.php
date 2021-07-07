<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader;

use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetaData;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;
use Synolia\SyliusGDPRPlugin\Validator\FakerOptionsValidator;

final class ArrayLoader implements LoaderInterface
{
    /** @var array */
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
        array $mapping,
        string $className,
        AttributeMetadataCollection $attributeMetaDataCollection
    ): AttributeMetadataCollection {
        foreach ($mapping[$className]['properties'] as $property => $options) {
            if (null === $options) {
                $attributeMetaDataCollection->add($property);

                continue;
            }
            $faker = $options['faker'] ?? null;
            $fakerArguments = $options['args'] ?? [];
            $isUnique = $options['unique'] ?? false;
            $prefix = $options['prefix'] ?? '';
            $value = $options['value'] ?? FakerOptionsValidator::DEFAULT_VALUE;
            $attributeMetaData = new AttributeMetaData($faker, $fakerArguments, $isUnique, $prefix, $value);

            $attributeMetaDataCollection->add($property, $attributeMetaData);
        }

        return $attributeMetaDataCollection;
    }
}
