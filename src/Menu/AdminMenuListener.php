<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'sylius.menu.admin.main', method: 'addAdminMenuItems')]
final class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $gdprMenu = $menu->addChild('gdpr');
        $gdprMenu->setLabel('sylius.ui.admin.synolia_gdpr.customer.gdpr_title');
        $gdprMenu->setLabelAttribute('icon', 'tabler:database-star');
        $gdprMenu
            ->addChild('sylius.ui.admin.synolia_gdpr.advanced_actions.title', [
                'route' => 'synolia_sylius_gdpr_admin_advanced_actions',
            ])
        ;
    }
}
