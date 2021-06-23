<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Controller;

use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Synolia\SyliusGDPRPlugin\Event\AfterAnonymize;
use Synolia\SyliusGDPRPlugin\Provider\AnonymizerInterface;

class AnonymizationController extends AbstractController
{
    public function __invoke(
        Request $request,
        CustomerRepositoryInterface $customerRepository,
        AnonymizerInterface $anonymizer,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        $id = $request->get('id');
        if (null === $id) {
            throw new \InvalidArgumentException('No customer provided');
        }

        $customer = $customerRepository->find($id);
        if (!$customer instanceof CustomerInterface) {
            throw new NotFoundHttpException('Customer not found.');
        }

        $email = $customer->getEmail();

        $anonymizer->anonymize($customer);

        $customerRepository->add($customer);

        $eventDispatcher->dispatch(new AfterAnonymize($customer, ['email' => $email]));

        return $this->redirectToRoute('sylius_admin_customer_show', ['id' => $customer->getId()]);
    }
}
