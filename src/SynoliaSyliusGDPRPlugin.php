<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Synolia\SyliusGDPRPlugin\DependencyInjection\CompilerPass\RegisterAdvancedActionsFormDataProcessorsPass;
use Synolia\SyliusGDPRPlugin\DependencyInjection\CompilerPass\RegisterAnonymizationLoader;

final class SynoliaSyliusGDPRPlugin extends Bundle
{
    use SyliusPluginTrait;

    public const VERSION = '1.1.0';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new RegisterAnonymizationLoader());
        $container->addCompilerPass(new RegisterAdvancedActionsFormDataProcessorsPass());
    }
}
