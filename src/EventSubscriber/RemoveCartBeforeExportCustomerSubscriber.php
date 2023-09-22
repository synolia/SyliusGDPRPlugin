<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\EventSubscriber;

use Sylius\Component\Core\Model\OrderInterface as CoreOrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Synolia\SyliusGDPRPlugin\Event\BeforeExportCustomerData;

class RemoveCartBeforeExportCustomerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeExportCustomerData::class => ['process'],
        ];
    }

    public function process(BeforeExportCustomerData $beforeExportCustomerData): void
    {
        $carts = $this->orderRepository->findBy([
            'customer' => $beforeExportCustomerData->getCustomer(),
            'state' => OrderInterface::STATE_CART,
        ]);

        /** @var CoreOrderInterface $cart */
        foreach ($carts as $cart) {
            $this->orderRepository->remove($cart);
        }
    }
}
