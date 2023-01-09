<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Event;

use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class AfterCustomerAnonymize extends Event
{
    public function __construct(private CustomerInterface $customer, private string $oldEmail)
    {
    }

    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }

    public function getOldEmail(): string
    {
        return $this->oldEmail;
    }
}
