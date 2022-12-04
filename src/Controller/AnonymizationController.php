<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Synolia\SyliusGDPRPlugin\Event\AfterCustomerAnonymize;
use Synolia\SyliusGDPRPlugin\Event\BeforeCustomerAnonymize;
use Synolia\SyliusGDPRPlugin\Provider\AnonymizerInterface;

class AnonymizationController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private CustomerRepositoryInterface $customerRepository;

    private AnonymizerInterface $anonymizer;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        CustomerRepositoryInterface $customerRepository,
        AnonymizerInterface $anonymizer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->customerRepository = $customerRepository;
        $this->anonymizer = $anonymizer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(Request $request, string $id): Response
    {
        $customer = $this->customerRepository->find($id);
        if (!$customer instanceof CustomerInterface) {
            $request->getSession()->getFlashBag()->add('error', 'sylius.ui.admin.sylius_gdpr.customer.not_found');

            return $this->redirectToRoute('sylius_admin_customer_index');
        }

        $this->eventDispatcher->dispatch(new BeforeCustomerAnonymize($customer));

        /** @var string $email */
        $email = $customer->getEmail();

        $this->anonymizer->anonymize($customer);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new AfterCustomerAnonymize($customer, $email));

        $request->getSession()->getFlashBag()->add('success', 'sylius.ui.admin.synolia_gdpr.success');

        return $this->redirectToRoute('sylius_admin_customer_show', ['id' => $customer->getId()]);
    }
}
