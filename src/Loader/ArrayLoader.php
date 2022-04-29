<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader;

use Doctrine\Common\Util\ClassUtils;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetaData;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;
use Synolia\SyliusGDPRPlugin\Validator\FakerOptionsValidator;

final class ArrayLoader implements LoaderInterface
{
    private array $mappings;

    public function __construct(array $mappings = [])
    {
        $this->mappings = $mappings;
    }

    public function loadClassMetadata(string $className): AttributeMetadataCollection
    {
        $attributeMetaDataCollection = new AttributeMetadataCollection();

        foreach ($this->mappings as $mapping) {
            $realClassName = $this->getMapping($mapping, $className);
            if (null === $realClassName) {
                continue;
            }

            $attributeMetaDataCollection = $this->assignAttributeMetaDataCollection(
                $mapping,
                $realClassName,
                $attributeMetaDataCollection
            );
        }

        return $attributeMetaDataCollection;
    }

    private function getMapping(array $mapping, string $className): ?string
    {
        if (array_key_exists($className, $mapping)) {
            return $className;
        }

        $parentClass = ClassUtils::newReflectionClass($className)->getParentClass();
        if (false === $parentClass) {
            return null;
        }

        return $this->getMapping($mapping, $parentClass->getName());
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
