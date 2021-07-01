<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Validator;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpFoundation\Response;
use Synolia\SyliusGDPRPlugin\Exception\GDPRPropertyException;

final class ArrayMappingValidator
{
    /** @var int */
    private const OPTIONS_LENGTH = 5;

    public function checkParse(array $mapping, string $className): void
    {
        $this->checkClassName($className);
        $this->checkPropertyKey($mapping);
        $this->checkProperty($mapping, $className);
        foreach ($mapping['properties'] as $propertyOptions) {
            if (null === $propertyOptions) {
                continue;
            }
            $this->checkPropertyOptions($propertyOptions);
        }
    }

    private function checkClassName(string $className): void
    {
        if (!\class_exists($className)) {
            throw new \LogicException('Class ' . $className . ' does not exist.');
        }
    }

    private function checkPropertyKey(array $mapping): void
    {
        if (!isset($mapping['properties'])) {
            throw new \LogicException(\array_key_first($mapping) . ' is not supported, try properties instead.');
        }
    }

    private function checkProperty(array $mapping, string $className): void
    {
        $class = ClassUtils::newReflectionClass($className);
        $propertiesMapping = \array_keys($mapping['properties']);
        foreach ($propertiesMapping as $propertyMapping) {
            try {
                $class->getProperty((string) $propertyMapping);
            } catch (\Exception $exception) {
                throw new GDPRPropertyException(
                    'The property ' . $propertyMapping . ' does not exist in entity ' . $className . '.',
                    Response::HTTP_NOT_FOUND,
                    $exception
                );
            }

            continue;
        }
    }

    private function checkPropertyOptions(array $options): void
    {
        if (self::OPTIONS_LENGTH < \count($options) || 0 === \count($options)) {
            throw new \LogicException('Anonymization expected 1 to 5 properties ' . \count($options) . ' given.');
        }

        new FakerOptionsValidator($options);
    }
}
