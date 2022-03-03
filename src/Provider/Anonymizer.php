<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Provider;

use Doctrine\Common\Util\ClassUtils;
use Faker\Factory;
use Faker\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Synolia\SyliusGDPRPlugin\Event\AfterAnonymize;
use Synolia\SyliusGDPRPlugin\Event\BeforeAnonymize;
use Synolia\SyliusGDPRPlugin\Exception\GDPRValueException;
use Synolia\SyliusGDPRPlugin\Loader\LoaderChain;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetaData;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetaDataInterface;
use Synolia\SyliusGDPRPlugin\Validator\FakerOptionsValidator;

final class Anonymizer implements AnonymizerInterface
{
    private const TYPE_VALUES = [
        'bool',
        'string',
        'int',
        'float',
    ];

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

        $className = ClassUtils::getClass($entity);
        $attributeMetadataCollection = $this->loaderChain->loadClassMetadata($className);
        $attributeMetadataCollection = $attributeMetadataCollection->get();

        foreach ($attributeMetadataCollection as $propertyName => $attributeMetaData) {
            if ($this->isIterative($entity, $className, $propertyName)) {
                $getter = 'get' . ucfirst($propertyName);
                foreach ($entity->$getter() as $item) {
                    if (!is_object($item)) {
                        if (!$attributeMetaData instanceof AttributeMetaData) {
                            $this->logger->error(sprintf('The attribute %s has no Attribute meta data and is not an object.', $propertyName));

                            continue;
                        }
                        $this->anonymizeProcess($entity, $reset, $maxRetries, $className, $propertyName, $attributeMetaData);

                        continue;
                    }

                    $this->anonymize($item, $reset, $maxRetries);
                }

                continue;
            }

            if ($this->isSubclass($entity, $className, $propertyName)) {
                $getter = 'get' . ucfirst($propertyName);
                if ($entity->$getter() instanceof \DateTime && $attributeMetaData instanceof AttributeMetaData) {
                    $this->anonymizeProcess($entity, $reset, $maxRetries, $className, $propertyName, $attributeMetaData);
                }

                $this->anonymize($entity->$getter(), $reset, $maxRetries);

                continue;
            }

            if (!$attributeMetaData instanceof AttributeMetaData) {
                $this->logger->error(sprintf('The attribute %s has no Attribute meta data and is not an object.', $propertyName));

                continue;
            }

            $this->anonymizeProcess($entity, $reset, $maxRetries, $className, $propertyName, $attributeMetaData);
        }

        $this->eventDispatcher->dispatch(new AfterAnonymize($entity, ['entity' => $clonedEntity]));
    }

    private function anonymizeProcess(
        Object $entity,
        bool $reset,
        int $maxRetries,
        string $className,
        string $propertyName,
        AttributeMetaData $attributeMetaData
    ): void {
        $propertyExtractor = (new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]));

        /** @var array<int, Type>|null $types */
        $types = $propertyExtractor->getTypes($className, $propertyName);
        $type = null !== $types ? $types[0]->getBuiltinType() : 'string';
        $value = $attributeMetaData->getValue();
        if (FakerOptionsValidator::DEFAULT_VALUE !== $value) {
            if (is_array($value)) {
                $this->setValue($entity, $propertyName, $type, $value);

                return;
            }

            $this->setValue(
                $entity,
                $propertyName,
                $type,
                sprintf('%s%s', (string) $attributeMetaData->getPrefix(), (string) $value)
            );

            return;
        }

        if (true === $attributeMetaData->isUnique()) {
            $value = $this->faker->unique($reset, $maxRetries)->format($attributeMetaData->getFaker(), $attributeMetaData->getArgs());
            $this->setUniqueValue($entity, $value, $type, $propertyName, $attributeMetaData);

            return;
        }

        if (null === $attributeMetaData->getFaker()) {
            $this->setValue(
                $entity,
                $propertyName,
                $type,
                null
            );

            return;
        }

        $value = $this->faker->format($attributeMetaData->getFaker(), $attributeMetaData->getArgs());
        if (is_object($value)) {
            if (!in_array($type, self::TYPE_VALUES, true)) {
                $this->propertyAccess->setValue(
                    $entity,
                    $propertyName,
                    $value
                );

                return;
            }

            throw new GDPRValueException('Value or type don\'t match with object');
        }

        $this->setValue(
            $entity,
            $propertyName,
            $type,
            is_array($value) ? $value : sprintf('%s%s', (string) $attributeMetaData->getPrefix(), (string) $value)
        );
    }

    /** @param mixed $value */
    private function setUniqueValue(Object $entity, $value, string $type, string $propertyName, AttributeMetaDataInterface $attributeMetaData): void
    {
        if (is_object($value)) {
            if (!in_array($type, self::TYPE_VALUES, true)) {
                $this->propertyAccess->setValue(
                    $entity,
                    $propertyName,
                    $value
                );

                return;
            }

            throw new GDPRValueException('Value or type don\'t match with object');
        }
        $this->setValue(
            $entity,
            $propertyName,
            $type,
            sprintf('%s%s', (string) $attributeMetaData->getPrefix(), (string) $value)
        );
    }

    /** @param array|string|bool|null $value */
    private function setValue(object $entity, string $propertyName, string $type, $value): void
    {
        if (is_array($value)) {
            if ('array' === $type) {
                $this->propertyAccess->setValue(
                    $entity,
                    $propertyName,
                    $value
                );

                return;
            }

            throw new GDPRValueException('Value or type don\'t match with array');
        }

        if ('int' === $type) {
            $this->propertyAccess->setValue(
                $entity,
                $propertyName,
                (int) $value
            );

            return;
        }

        if ('float' === $type) {
            $this->propertyAccess->setValue(
                $entity,
                $propertyName,
                (float) $value
            );
        }

        if ('bool' === $type) {
            $this->propertyAccess->setValue(
                $entity,
                $propertyName,
                (bool) $value
            );
        }

        $this->propertyAccess->setValue(
            $entity,
            $propertyName,
            $value
        );
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
