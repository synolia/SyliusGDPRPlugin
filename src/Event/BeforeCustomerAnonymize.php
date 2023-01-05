<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Event;

use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeCustomerAnonymize extends Event
{
    public function __construct(private CustomerInterface $customer)
    {
    }

    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }
}
