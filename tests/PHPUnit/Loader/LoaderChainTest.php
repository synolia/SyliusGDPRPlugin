<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Loader;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Synolia\SyliusGDPRPlugin\Loader\LoaderChain;
use Synolia\SyliusGDPRPlugin\Loader\Mapping\AttributeMetadataCollection;

final class LoaderChainTest extends KernelTestCase
{
    /** @var LoaderChain */
    private $loadChain;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->loadChain = self::$container->get(LoaderChain::class);
    }

    public function testLoadClassMetaData(): void
    {
        $attributeMetaDataCollection = $this->loadChain->loadClassMetadata('Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures\YamlFoo');
        $this->assertInstanceOf(AttributeMetadataCollection::class, $attributeMetaDataCollection);
        $this->assertSame('email', $attributeMetaDataCollection->get()['bar']->getFaker());
    }
}
