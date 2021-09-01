<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Processor\AdvancedActions;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Forms;
use Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomersWithoutAnyOrdersBeforeType;
use Synolia\SyliusGDPRPlugin\Processor\AdvancedActions\CompositeAdvancedActionsFormDataProcessor;

class AnonymizeCustomerWithoutAnyOrdersBeforeProcessorTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->manager->beginTransaction();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->manager->rollback();
    }

    public function testAnonymizeCustomers(): void
    {
        /** @var array<int, CustomerInterface> $customers */
        $customers = $this->getContainer()->get('sylius.repository.customer')->findAll();
        $customers[0]->setCreatedAt(new \DateTime());
        $customers[1]->setCreatedAt((new \Datetime())->sub(new \DateInterval('P2D')));
        foreach ($customers[1]->getOrders() as $order) {
            $order->setState(OrderInterface::STATE_CART);
        }
        if (0 === $customers[1]->getOrders()->count()) {
            $orders = $this->getContainer()->get('sylius.repository.order')->findBy(['customer' => $customers[5]]);
            foreach ($orders as $order) {
                $order->setState(OrderInterface::STATE_CART);
                $order->setCustomer($customers[1]);
            }
        }

        $customers[2]->setCreatedAt((new \Datetime())->sub(new \DateInterval('P2D')));
        if (0 === $customers[1]->getOrders()->count()) {
            $orders = $this->getContainer()->get('sylius.repository.order')->findBy(['customer' => $customers[4]]);
            foreach ($orders as $order) {
                $order->setState(OrderInterface::STATE_CART);
                $order->setCustomer($customers[2]);
            }
        }

        $customers[2]->getOrders()->first()->setState(OrderInterface::STATE_NEW);

        $this->manager->flush();

        $form = Forms::createFormFactoryBuilder()->getFormFactory()->create(
            AnonymizeCustomersWithoutAnyOrdersBeforeType::class,
            ['anonymize_customer_without_any_orders_before_date' => (new \Datetime())->sub(new \DateInterval('P1D'))]
        );

        /** @var CompositeAdvancedActionsFormDataProcessor $composite */
        $composite = $this->getContainer()->get(CompositeAdvancedActionsFormDataProcessor::class);
        $composite->process(AnonymizeCustomersWithoutAnyOrdersBeforeType::class, $form);

        $this->assertStringContainsString('anonymized-', $customers[1]->getEmail());
        $this->assertStringNotContainsString('anonymized-', $customers[0]->getEmail());
        $this->assertStringNotContainsString('anonymized-', $customers[2]->getEmail());
    }
}
