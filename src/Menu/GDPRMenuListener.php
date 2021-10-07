<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class GDPRMenuListener
{
    public function addGDPRMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $gdprMenu = $menu->addChild('gdpr');
        $gdprMenu
            ->addChild('app.ui.gdpr.advanced_actions', [
                'route' => 'sylius_gdpr_advanced_actions',
            ]);
    }
}
