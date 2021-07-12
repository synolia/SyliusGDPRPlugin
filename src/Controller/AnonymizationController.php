<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Controller;

use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Synolia\SyliusGDPRPlugin\Event\AfterCustomerAnonymize;
use Synolia\SyliusGDPRPlugin\Provider\AnonymizerInterface;

class AnonymizationController extends AbstractController
{
    public function __invoke(
        string $id,
        CustomerRepositoryInterface $customerRepository,
        AnonymizerInterface $anonymizer,
        EventDispatcherInterface $eventDispatcher,
        ParameterBagInterface $parameterBag
    ): Response {
        $customer = $customerRepository->find($id);
        if (!$customer instanceof CustomerInterface) {
            $parameterBag->set('error', 'sylius.ui.admin.sylius_gdpr.customer.not_found');
        }

        $email = $customer->getEmail();

        $anonymizer->anonymize($customer);

        $customerRepository->add($customer);

        $eventDispatcher->dispatch(new AfterCustomerAnonymize($customer, $email));

        return $this->redirectToRoute('sylius_admin_customer_show', ['id' => $customer->getId()]);
    }
}
