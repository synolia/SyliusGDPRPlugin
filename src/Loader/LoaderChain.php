<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Loader;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;

final class LoaderChain implements LoaderInterface
{
    /** @var LoaderInterface[] */
    private array $loaders = [];

    public function __construct(
        #[AutowireIterator(LoaderInterface::class, defaultPriorityMethod: 'getDefaultPriority', exclude: [self::class])]
        iterable $loaders,
    ) {
        foreach ($loaders as $loader) {
            if (!$loader instanceof LoaderInterface) {
                throw new \LogicException('Not an anonymization loader');
            }
            $this->addLoader($loader);
        }
    }

    public function addLoader(LoaderInterface $loader): void
    {
        if ($loader instanceof self) {
            return;
        }

        $this->loaders[] = $loader;
    }

    public function loadClassMetadata(string $className): AttributeMetadataCollection
    {
        $fullCollect = new AttributeMetadataCollection();
        foreach ($this->loaders as $loader) {
            $collection = $loader->loadClassMetadata($className);

            foreach ($collection->get() as $property => $attributeMetadata) {
                $fullCollect->add($property, $attributeMetadata);
            }
        }

        return $fullCollect;
    }

    public static function getDefaultPriority(): int
    {
        return 0;
    }
}
