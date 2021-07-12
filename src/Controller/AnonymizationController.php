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
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var AnonymizerInterface */
    private $anonymizer;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var ParameterBagInterface */
    private $parameterBag;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        AnonymizerInterface $anonymizer,
        EventDispatcherInterface $eventDispatcher,
        ParameterBagInterface $parameterBag)
    {
        $this->customerRepository = $customerRepository;
        $this->anonymizer = $anonymizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->parameterBag = $parameterBag;
    }

    public function __invoke(string $id): Response
    {
        $customer = $this->customerRepository->find($id);
        if (!$customer instanceof CustomerInterface) {
            $this->parameterBag->set('error', 'sylius.ui.admin.sylius_gdpr.customer.not_found');
        }

        $email = $customer->getEmail();

        $this->anonymizer->anonymize($customer);

        $this->customerRepository->add($customer);

        $this->eventDispatcher->dispatch(new AfterCustomerAnonymize($customer, $email));

        return $this->redirectToRoute('sylius_admin_customer_show', ['id' => $customer->getId()]);
    }
}
