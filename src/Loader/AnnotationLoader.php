<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader;

use Doctrine\Common\Annotations\Reader;
use Sylius\Component\Core\Model\ShopUser;
use Synolia\SyliusGDPRPlugin\Annotation\Anonymize;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetaData;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;

final class AnnotationLoader implements LoaderInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @throws \ReflectionException
     */
    public function loadClassMetadata(string $className): AttributeMetadataCollection
    {
        $reflectionClass = new \ReflectionClass($className);
        $properties = $reflectionClass->getProperties();
        $attributeMetaDataCollection = new AttributeMetadataCollection();
        foreach ($properties as $property) {
            /** @var Anonymize $annotation */
            $annotation = $this->annotationReader->getPropertyAnnotation(
                $property,
                Anonymize::class
            );

            if (!$annotation instanceof Anonymize) {
                continue;
            }

            if (null === $annotation->faker) {
                throw new \LogicException('Faker annotation can\'t be empty');
            }

            $attributeMetaData = new AttributeMetaData($annotation->faker, $annotation->args, $annotation->unique);

            $attributeMetaData->setFaker($annotation->faker)
                ->setArgs($annotation->args)
                ->setUnique($annotation->unique)
            ;
            $attributeMetaDataCollection->add($property->name, $attributeMetaData);
        }

        return $attributeMetaDataCollection;
    }
}
