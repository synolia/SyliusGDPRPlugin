<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Synolia\SyliusGDPRPlugin\Loader\ArrayLoader;
use Synolia\SyliusGDPRPlugin\Loader\LoaderChain;

final class RegisterAnonymizationLoader implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(LoaderChain::class)) {
            return;
        }

        $arrayLoader = $container->getDefinition(ArrayLoader::class);
        $arrayLoader->setArgument(
            0,
            $container->getParameter('synolia_anonymization_mapping'),
        );

        $container->getParameterBag()->remove('synolia_anonymization_mapping');
    }
}
