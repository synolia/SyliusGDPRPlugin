<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Validator;

final class ArrayMappingValidator
{
    /**
     * @var int
     */
    private const OPTIONS_LENGTH = 3;

    public function checkParse(array $mapping, string $className): void
    {
        $this->checkClassName($className);
        $this->checkPropertyKey($mapping);
        $this->checkProperty($mapping, $className);
        foreach ($mapping['properties'] as $propertyOptions) {
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
        $class = new \ReflectionClass($className);
        $propertiesMapping = \array_keys($mapping['properties']);
        foreach ($propertiesMapping as $propertyMapping) {
            try {
                $class->getProperty((string) $propertyMapping);
            } catch (\Exception $exception) {
                throw new \LogicException('The property ' . $propertyMapping . ' does not exist in entity ' . $className . '.');
            }
            continue;
        }
    }

    private function checkPropertyOptions(array $options): void
    {
        if (\count($options) > self::OPTIONS_LENGTH || 0 === \count($options)) {
            throw new \LogicException('Anonymization expected 1 to 3 properties ' . \count($options) . ' given.');
        }
        new FakerOptionsValidator($options);
    }
}
