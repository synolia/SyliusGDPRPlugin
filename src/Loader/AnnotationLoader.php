<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Synolia\SyliusGDPRPlugin\Annotation\Anonymize;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetaData;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;

final class AnnotationLoader implements LoaderInterface
{
    private Reader $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /** @throws \ReflectionException */
    public function loadClassMetadata(string $className): AttributeMetadataCollection
    {
        $reflectionClass = ClassUtils::newReflectionClass($className);
        $properties = $reflectionClass->getProperties();
        $attributeMetaDataCollection = new AttributeMetadataCollection();
        foreach ($properties as $property) {
            $annotation = $this->annotationReader->getPropertyAnnotation(
                $property,
                Anonymize::class
            );

            if (!$annotation instanceof Anonymize) {
                continue;
            }

            $attributeMetaData = new AttributeMetaData($annotation->faker, $annotation->args, $annotation->unique, $annotation->prefix, $annotation->value);

            $attributeMetaDataCollection->add($property->name, $attributeMetaData);
        }

        return $attributeMetaDataCollection;
    }
}
