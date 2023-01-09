<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader\Mapping;

final class AttributeMetadataCollection
{
    /** @var array<string, ?AttributeMetaDataInterface> */
    private array $elements;

    public function __construct(array $elements = [])
    {
        foreach ($elements as $element) {
            if (!$element instanceof AttributeMetaDataInterface) {
                throw new \LogicException('Not an attribute metadata');
            }
        }

        $this->elements = $elements;
    }

    public function add(string $property, ?AttributeMetaDataInterface $attributeMetaData = null): self
    {
        $this->elements[$property] = $attributeMetaData;

        return $this;
    }

    /** @return array<string, ?AttributeMetaDataInterface> */
    public function get(): array
    {
        return $this->elements;
    }
}
