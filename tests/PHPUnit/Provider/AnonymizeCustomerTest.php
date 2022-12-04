<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Synolia\SyliusGDPRPlugin\Controller\AnonymizationController;

class AnonymizeCustomerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testAnonymizeCustomer(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        /** @var SessionFactoryInterface $sessionFactory */
        $sessionFactory = static::getContainer()->get('session.factory');
        $session = $sessionFactory->createSession();
        $request = new Request();
        $request->setSession($session);

        $customer = static::getContainer()->get('sylius.factory.customer')->createNew();
        /** @var CustomerInterface $customer */
        $customer = $entityManager->getRepository(get_class($customer))->findOneBy([]);
        $this->assertInstanceOf(CustomerInterface::class, $customer);

        $beforeAnonymizationEmail = $customer->getEmail();

        $address = $this->createAddress($entityManager);
        $customer->setDefaultAddress($address);
        $order = $this->createPaymentAndAssignOrder($entityManager, $customer);

        $entityManager->flush();

        $addressFirstName = $address->getFirstName();

        $anonymizationController = static::getContainer()->get(AnonymizationController::class);
        $anonymizationController->__invoke($request, (string) $customer->getId());

        $entityManager->refresh($customer);
        $entityManager->refresh($order);

        $this->assertNotSame($beforeAnonymizationEmail, $customer->getEmail());
        // Test if the subsclass are also anonymized
        $this->assertNotSame($addressFirstName, $customer->getDefaultAddress()->getFirstName());
        $this->assertSame(['anonymized-details'], $order->getPayments()->first()->getDetails());
    }

    private function createAddress(EntityManagerInterface $entityManager): AddressInterface
    {
        /** @var AddressInterface $address */
        $address = static::getContainer()->get('sylius.factory.address')->createNew();
        $address->setFirstName('test');
        $address->setLastName('test');
        $address->setPostcode('test');
        $address->setCity('test');
        $address->setPhoneNumber('test');
        $address->setStreet('test');
        $address->setCountryCode('TE');

        $entityManager->persist($address);
        $entityManager->flush();

        return $address;
    }

    private function createPaymentAndAssignOrder(EntityManagerInterface $entityManager, CustomerInterface $customer): OrderInterface
    {
        $order = static::getContainer()->get('sylius.factory.order')->createNew();
        /** @var OrderInterface $order */
        $order = $entityManager->getRepository(get_class($order))->findOneBy([]);
        /** @var PaymentInterface $payment */
        $payment = static::getContainer()->get('sylius.factory.payment')->createNew();
        $order->getPayments()->clear();

        $payment->setAmount(100);
        $paymentMethod = static::getContainer()->get('sylius.factory.payment_method')->createNew();
        $payment->setMethod($entityManager->getRepository(get_class($paymentMethod))->findOneBy([]));
        $payment->setCurrencyCode('EUR');
        $payment->setDetails(['test']);

        $entityManager->persist($payment);

        $order->addPayment($payment);
        $order->setCustomer($customer);

        $entityManager->flush();

        return $order;
    }
}
