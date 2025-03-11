<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader;

use Doctrine\Common\Util\ClassUtils;
use Synolia\SyliusGDPRPlugin\Attribute\Anonymize;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetaData;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;

final readonly class AttributeLoader implements LoaderInterface
{
    /** @throws \ReflectionException */
    public function loadClassMetadata(string $className): AttributeMetadataCollection
    {
        $reflectionClass = ClassUtils::newReflectionClass($className);
        $properties = $reflectionClass->getProperties();
        $attributeMetaDataCollection = new AttributeMetadataCollection();
        foreach ($properties as $property) {
            $attributes = $property->getAttributes(Anonymize::class, \ReflectionAttribute::IS_INSTANCEOF);

            if (\count($attributes) === 0) {
                continue;
            }
            $attributesInstances = [];
            foreach ($attributes as $attribute) {
                $attributesInstances[] = $attribute->newInstance();
            }
            /** @var Anonymize $attribute */
            $attribute = $attributesInstances[0];
            $attributeMetaData = new AttributeMetaData($attribute->faker, $attribute->args, $attribute->unique, $attribute->prefix, $attribute->value);

            $attributeMetaDataCollection->add($property->name, $attributeMetaData);
        }

        return $attributeMetaDataCollection;
    }
}
