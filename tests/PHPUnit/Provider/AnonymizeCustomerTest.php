<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Synolia\SyliusGDPRPlugin\Controller\AnonymizationController;

class AnonymizeCustomerTest extends KernelTestCase
{
    public function testAnonymizeCustomer(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        $customer = $this->getContainer()->get('sylius.factory.customer')->createNew();
        /** @var CustomerInterface $customer */
        $customer = $entityManager->getRepository(get_class($customer))->findOneBy([]);
        $this->assertInstanceOf(CustomerInterface::class, $customer);

        $beforeAnonymizationEmail = $customer->getEmail();

        $address = $this->createAddress($entityManager);
        $customer->setDefaultAddress($address);

        $entityManager->flush();

        $addressFirstName = $address->getFirstName();

        $anonymizationController = $this->getContainer()->get(AnonymizationController::class);
        $anonymizationController->__invoke((string) $customer->getId());

        $entityManager->refresh($customer);

        $this->assertNotSame($beforeAnonymizationEmail, $customer->getEmail());
        // Test if the subsclass are also anonymized
        $this->assertNotSame($addressFirstName, $customer->getDefaultAddress()->getFirstName());
    }

    private function createAddress(EntityManagerInterface $entityManager): AddressInterface
    {
        /** @var AddressInterface $address */
        $address = $this->getContainer()->get('sylius.factory.address')->createNew();
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
}
