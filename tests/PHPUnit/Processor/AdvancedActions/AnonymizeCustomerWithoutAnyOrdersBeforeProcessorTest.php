<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Processor\AdvancedActions;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Forms;
use Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomersWithoutAnyOrdersBeforeType;
use Synolia\SyliusGDPRPlugin\Processor\AdvancedActions\CompositeAdvancedActionsFormDataProcessor;
use Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Processor\WithSessionTrait;

class AnonymizeCustomerWithoutAnyOrdersBeforeProcessorTest extends KernelTestCase
{
    use WithSessionTrait;

    private ?EntityManagerInterface $manager = null;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->manager = static::getContainer()->get(EntityManagerInterface::class);
        $this->manager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->manager->rollback();

        parent::tearDown();
    }

    public function testAnonymizeCustomers(): void
    {
        $this->createSession();

        /** @var array<int, CustomerInterface> $customers */
        $customers = static::getContainer()->get('sylius.repository.customer')->findAll();
        $customers[0]->setCreatedAt(new \DateTime());
        $customers[1]->setCreatedAt((new \Datetime())->sub(new \DateInterval('P2D')));

        $this->createOrder($customers[1], OrderInterface::STATE_CART);
        foreach ($customers[1]->getOrders() as $order) {
            $order->setState(OrderInterface::STATE_CART);
        }

        $customers[2]->setCreatedAt((new \Datetime())->sub(new \DateInterval('P2D')));
        $this->createOrder($customers[2], OrderInterface::STATE_CART);
        $this->createOrder($customers[2], OrderInterface::STATE_NEW);

        $customers[3]->setCreatedAt((new \Datetime())->sub(new \DateInterval('P2D')));
        /** @var OrderInterface $order */
        foreach ($customers[3]->getOrders() as $order) {
            static::getContainer()->get(EntityManagerInterface::class)->remove($order);
        }

        $this->manager->flush();

        $form = Forms::createFormFactoryBuilder()->getFormFactory()->create(
            AnonymizeCustomersWithoutAnyOrdersBeforeType::class,
            ['anonymize_customer_without_any_orders_before_date' => (new \Datetime())->sub(new \DateInterval('P1D'))],
        );

        /** @var CompositeAdvancedActionsFormDataProcessor $composite */
        $composite = static::getContainer()->get(CompositeAdvancedActionsFormDataProcessor::class);
        $composite->process(AnonymizeCustomersWithoutAnyOrdersBeforeType::class, $form);

        $this->assertStringContainsString('anonymized-', $customers[3]->getEmail());
        $this->assertStringContainsString('anonymized-', $customers[1]->getEmail());
        $this->assertStringNotContainsString('anonymized-', $customers[0]->getEmail());
        $this->assertStringNotContainsString('anonymized-', $customers[2]->getEmail());
    }

    private function createOrder(CustomerInterface $customer, string $state): OrderInterface
    {
        /** @var OrderInterface $order */
        $order = static::getContainer()->get('sylius.factory.order')->createNew();
        $order->setCustomer($customer);
        $order->setState($state);
        $order->setPaymentState('fake');
        $order->setCheckoutState('fake');
        $order->setShippingState('fake');
        $order->setTokenValue((string) random_int(0, 100000000));
        $order->setCurrencyCode('EUR');
        $order->setLocaleCode('en_EN');

        static::getContainer()->get(EntityManagerInterface::class)->persist($order);

        return $order;
    }
}
