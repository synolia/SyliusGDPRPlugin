<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Event;

use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class AfterCustomerAnonymize extends Event
{
    /** @var CustomerInterface */
    private $customer;

    /** @var string */
    private $oldEmail;

    public function __construct(CustomerInterface $customer, string $oldEmail)
    {
        $this->customer = $customer;
        $this->oldEmail = $oldEmail;
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
