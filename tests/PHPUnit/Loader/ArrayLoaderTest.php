<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Loader;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Synolia\SyliusGDPRPlugin\Loader\ArrayLoader;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;

final class ArrayLoaderTest extends KernelTestCase
{
    /** @var ArrayLoader */
    private $arrayLoader;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->arrayLoader = self::$container->get(ArrayLoader::class);
    }

    public function testParseConfigForPathReturnEmail(): void
    {
        $attributeMetaDataCollection = $this->arrayLoader->loadClassMetadata(
            'Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\YamlFoo'
        );
        $this->assertInstanceOf(AttributeMetadataCollection::class, $attributeMetaDataCollection);
        $this->assertSame('email', $attributeMetaDataCollection->get()['bar']->getFaker());
        $this->assertSame([], $attributeMetaDataCollection->get()['bar']->getArgs());
    }

    public function testParseConfigForPathReturnEmptyElementsAttributeMetaDataCollection(): void
    {
        $this->expectException(\ReflectionException::class);
        $this->arrayLoader->loadClassMetadata(
            'Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\NotEvenReal'
        );
    }
}
