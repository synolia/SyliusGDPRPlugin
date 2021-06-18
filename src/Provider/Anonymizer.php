<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Provider;

use Doctrine\Common\Util\ClassUtils;
use Faker\Factory;
use Faker\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Synolia\SyliusGDPRPlugin\Loader\LoaderChain;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetaData;

final class Anonymizer implements AnonymizerInterface
{
    /** @var Generator */
    private $faker;

    /** @var PropertyAccessorInterface */
    private $propertyAccess;

    /** @var LoaderChain */
    private $loaderChain;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        LoaderChain $loaderChain,
        LoggerInterface $logger
    ) {
        $this->faker = Factory::create();
        $this->propertyAccess = PropertyAccess::createPropertyAccessorBuilder()
            ->enableMagicCall()
            ->getPropertyAccessor();
        $this->loaderChain = $loaderChain;
        $this->logger = $logger;
    }

    public function anonymize($entity, $reset, $maxRetries): void
    {
        if (!\is_object($entity)) {
            throw new \LogicException('This is not an object.');
        }
        $className = ClassUtils::getRealClass(get_class($entity));
        $attributeMetadataCollection = $this->loaderChain->loadClassMetadata($className);
        $attributeMetadataCollection = $attributeMetadataCollection->get();
        /** @var AttributeMetaData $attributeMetaData */
        foreach ($attributeMetadataCollection as $propertyName => $attributeMetaData) {
            if ($this->isSubclass($entity, $className, $propertyName)) {
                $getter = 'get' . ucfirst($propertyName);
                $this->anonymize($entity->$getter(), $reset, $maxRetries);

                continue;
            }

            if (!$attributeMetaData instanceof AttributeMetaData) {
                $this->logger->error(sprintf('The attribute %s has no Attribute meta data and is not an object.', $propertyName));

                continue;
            }
            if (true === $attributeMetaData->isUnique()) {
                $value = $this->faker->unique($reset, $maxRetries)->format($attributeMetaData->getFaker(), $attributeMetaData->getArgs());
                $this->propertyAccess->setValue($entity, $propertyName, $value);

                continue;
            }

            $value = $this->faker->format($attributeMetaData->getFaker(), $attributeMetaData->getArgs());
            $this->propertyAccess->setValue($entity, $propertyName, $value);
        }
    }

    private function isSubclass($entity, string $className, string $propertyName): bool
    {
        $classReflection = ClassUtils::newReflectionClass($className);
        $getter = 'get' . ucfirst($propertyName);
        if (!$classReflection->getMethod($getter)) {
            return false;
        }

        return is_object($entity->$getter());
    }
}
