<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Provider;

use Doctrine\Common\Util\ClassUtils;
use Faker\Factory;
use Faker\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Synolia\SyliusGDPRPlugin\Event\AfterAnonymize;
use Synolia\SyliusGDPRPlugin\Event\BeforeAnonymize;
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

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        LoaderChain $loaderChain,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->faker = Factory::create();
        $this->propertyAccess = PropertyAccess::createPropertyAccessorBuilder()
            ->enableMagicCall()
            ->getPropertyAccessor();
        $this->loaderChain = $loaderChain;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    public function anonymize(Object $entity, bool $reset = false, int $maxRetries = 10000): void
    {
        $this->eventDispatcher->dispatch(new BeforeAnonymize($entity));

        $clonedEntity = clone $entity;

        $className = ClassUtils::getRealClass(get_class($entity));
        $attributeMetadataCollection = $this->loaderChain->loadClassMetadata($className);
        $attributeMetadataCollection = $attributeMetadataCollection->get();
        foreach ($attributeMetadataCollection as $propertyName => $attributeMetaData) {
            if ($this->isIterative($entity, $className, $propertyName)) {
                $getter = 'get' . ucfirst($propertyName);
                foreach ($entity->$getter() as $item) {
                    $this->anonymize($item, $reset, $maxRetries);
                }

                continue;
            }

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

        $this->eventDispatcher->dispatch(new AfterAnonymize($entity, ['entity' => $clonedEntity]));
    }

    private function isSubclass(object $entity, string $className, string $propertyName): bool
    {
        $classReflection = ClassUtils::newReflectionClass($className);
        $getter = 'get' . ucfirst($propertyName);

        try {
            $getter = $classReflection->getMethod($getter)->getName();
        } catch (\InvalidArgumentException | \ReflectionException $exception) {
            return false;
        }

        return is_object($entity->$getter());
    }

    private function isIterative(object $entity, string $className, string $propertyName): bool
    {
        $classReflection = ClassUtils::newReflectionClass($className);
        $getter = 'get' . ucfirst($propertyName);

        try {
            $getter = $classReflection->getMethod($getter)->getName();
        } catch (\InvalidArgumentException | \ReflectionException $exception) {
            return false;
        }

        return is_countable($entity->$getter());
    }
}
