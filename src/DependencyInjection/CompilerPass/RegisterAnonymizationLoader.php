<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Synolia\SyliusGDPRPlugin\Loader\ArrayLoader;
use Synolia\SyliusGDPRPlugin\Loader\LoaderChain;

final class RegisterAnonymizationLoader implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $services = $container->findTaggedServiceIds('anonymization_loader');
        $chainLoader = $container->getDefinition(LoaderChain::class);

        foreach (\array_keys($services) as $id) {
            $definition = $container->getDefinition($id);
            if (ArrayLoader::class === $definition->getClass()) {
                $definition->setArgument(
                    0,
                    $container->getParameter('synolia_anonymization_mapping')
                );
            }
            $chainLoader->addMethodCall('addLoader', [new Reference($id)]);
        }
        $container->getParameterBag()->remove('synolia_anonymization_mapping');
    }
}
