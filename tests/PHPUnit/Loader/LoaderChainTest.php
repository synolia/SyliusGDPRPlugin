<?php

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Loader;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Synolia\SyliusGDPRPlugin\Loader\LoaderChain;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;
use TypeError;

final class LoaderChainTest extends KernelTestCase
{
    /** @var LoaderChain */
    private $loadChain;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->loadChain = self::$container->get(LoaderChain::class);
    }

    public function testAddLoaderString(): void
    {
        $this->expectException(TypeError::class);
        $this->loadChain->addLoader('string');
    }

    public function testAddLoaderInt(): void
    {
        $this->expectException(TypeError::class);
        $this->loadChain->addLoader(2);
    }

    public function testLoadClassMetaData(): void
    {
        $attributeMetaDataCollection = $this->loadChain->loadClassMetadata('Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\YamlFoo');
        $this->assertInstanceOf(AttributeMetadataCollection::class, $attributeMetaDataCollection);
        $this->assertSame('email', $attributeMetaDataCollection->get()['bar']->getFaker());
    }
    public function testPassArrayLoadClassMetaData(): void
    {
        $this->expectException(TypeError::class);
        $this->loadChain->loadClassMetadata(['Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\YamlFoo']);
    }

    public function testPassIntLoadClassMetaData(): void
    {
        $this->expectException(\ReflectionException::class);
        $this->loadChain->loadClassMetadata(0);
    }
}
