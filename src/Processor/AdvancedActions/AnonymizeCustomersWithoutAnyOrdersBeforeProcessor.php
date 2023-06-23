<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor\AdvancedActions;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Synolia\SyliusGDPRPlugin\Processor\AnonymizerProcessor;

class AnonymizeCustomersWithoutAnyOrdersBeforeProcessor implements AdvancedActionsFormDataProcessorInterface
{
    private EntityManagerInterface $entityManager;

    private AnonymizerProcessor $anonymizerProcessor;

    private ParameterBagInterface $parameterBag;

    private RequestStack $requestStack;

    public function __construct(
        EntityManagerInterface $entityManager,
        AnonymizerProcessor $anonymizerProcessor,
        ParameterBagInterface $parameterBag,
        RequestStack $requestStack,
    ) {
        $this->entityManager = $entityManager;
        $this->anonymizerProcessor = $anonymizerProcessor;
        $this->parameterBag = $parameterBag;
        $this->requestStack = $requestStack;
    }

    /** @inheritdoc */
    public function process(string $formTypeClass, FormInterface $form): void
    {
        /** @var string $customer */
        $customer = $this->parameterBag->get('sylius.model.customer.class');
        /** @var string $order */
        $order = $this->parameterBag->get('sylius.model.order.class');
        /** @var array $data */
        $data = $form->getData();

        $customers = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from($customer, 'c')
            ->leftJoin($order, 'o', Join::WITH, 'o.customer = c')
            ->where('c.createdAt < :before')
            ->andWhere('o.state = :state OR o IS NULL')
            ->setParameter('before', $data['anonymize_customer_without_any_orders_before_date'])
            ->setParameter('state', OrderInterface::STATE_CART)
            ->getQuery()
            ->execute()
        ;

        $this->removeNoneEligibleCustomers($customers);

        $this->anonymizerProcessor->anonymizeEntities($customers);

        $this->requestStack->getSession()->getFlashBag()->add('success', sprintf('%d customers anonymized.', $this->anonymizerProcessor->getAnonymizedEntityCount()));
    }

    private function removeNoneEligibleCustomers(array &$customers): array
    {
        /** @var CustomerInterface $customer */
        foreach ($customers as $key => $customer) {
            foreach ($customer->getOrders() as $order) {
                if ($order->getState() === OrderInterface::STATE_CART) {
                    continue;
                }

                unset($customers[$key]);

                continue 2;
            }
        }

        return $customers;
    }

    public function getFormTypesClass(): array
    {
        return ['Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomersWithoutAnyOrdersBeforeType'];
    }
}
