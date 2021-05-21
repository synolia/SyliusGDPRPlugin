<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Provider;

use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Synolia\SyliusGDPRPlugin\Loader\LoaderChain;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetaData;

final class Anonymizer implements AnonymizerInterface
{
    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccess;

    /**
     * @var LoaderChain
     */
    private $loaderChain;

    public function __construct(LoaderChain $loaderChain)
    {
        $this->faker = Factory::create();
        $this->propertyAccess = PropertyAccess::createPropertyAccessorBuilder()
            ->enableMagicCall()
            ->getPropertyAccessor();
        $this->loaderChain = $loaderChain;
    }

    public function anonymize($result, $reset, $maxRetries): void
    {
        if (!\is_object($result)) {
            throw new \LogicException('This is not an object.');
        }
        $className = \get_class($result);
        $attributeMetadataCollection = $this->loaderChain->loadClassMetadata($className);
        $attributeMetadataCollection = $attributeMetadataCollection->get();
        /** @var AttributeMetaData $attributeMetaData */
        foreach ($attributeMetadataCollection as $propertyName => $attributeMetaData) {
            if (true === $attributeMetaData->isUnique()) {
                $value = $this->faker->unique($reset, $maxRetries)->format($attributeMetaData->getFaker(), $attributeMetaData->getArgs());
                $this->propertyAccess->setValue($result, $propertyName, $value);
                continue;
            }
            $value = $this->faker->format($attributeMetaData->getFaker(), $attributeMetaData->getArgs());
            $this->propertyAccess->setValue($result, $propertyName, $value);
        }
    }
}
