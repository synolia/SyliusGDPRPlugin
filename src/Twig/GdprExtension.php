<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class GdprExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sylius_plus_rbac_gdpr_has_permission', $this->hasPermission(...), ['needs_environment' => true]),
        ];
    }

    public function hasPermission(Environment $env, string $permission): bool
    {
        $function = $env->getFunction('sylius_plus_rbac_has_permission');

        if ($function instanceof TwigFunction && is_callable($function->getCallable())) {
            return $function->getCallable()($permission);
        }

        return true;
    }
}
