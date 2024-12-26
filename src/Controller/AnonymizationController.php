<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Synolia\SyliusGDPRPlugin\Event\AfterCustomerAnonymize;
use Synolia\SyliusGDPRPlugin\Event\BeforeCustomerAnonymize;
use Synolia\SyliusGDPRPlugin\Provider\AnonymizerInterface;

#[AsController]
class AnonymizationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly AnonymizerInterface $anonymizer,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route('/customers/{id}/anonymize', name: 'synolia_sylius_gdpr_admin_anonymize_customer', defaults: ['_sylius' => ['permission' => true, 'section' => 'admin', 'alias' => 'plugin_synolia_gdpr']], methods: ['GET|POST'])]
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
