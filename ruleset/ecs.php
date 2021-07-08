<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(dirname(__DIR__) . '/vendor/sylius-labs/coding-standard/ecs.php');

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        dirname(__DIR__) . '/src',
        dirname(__DIR__) . '/tests/PHPUnit',
    ]);
};
