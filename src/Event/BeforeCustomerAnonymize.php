<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Event;

use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeCustomerAnonymize extends Event
{
    /** @var CustomerInterface */
    private $customer;

    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }
}
