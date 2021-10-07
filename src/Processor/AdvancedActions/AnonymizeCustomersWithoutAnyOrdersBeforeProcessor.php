<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor\AdvancedActions;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Synolia\SyliusGDPRPlugin\Processor\AnonymizerProcessor;

class AnonymizeCustomersWithoutAnyOrdersBeforeProcessor implements AdvancedActionsFormDataProcessorInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AnonymizerProcessor */
    private $anonymizerProcessor;

    /** @var ParameterBagInterface */
    private $parameterBag;

    public function __construct(EntityManagerInterface $entityManager, AnonymizerProcessor $anonymizerProcessor, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $entityManager;
        $this->anonymizerProcessor = $anonymizerProcessor;
        $this->parameterBag = $parameterBag;
    }

    /** {@inheritdoc} */
    public function process(string $formTypeClass, FormInterface $form): void
    {
        /** @var string $customer */
        $customer = $this->parameterBag->get('sylius.model.customer.class');
        /** @var string $order */
        $order = $this->parameterBag->get('sylius.model.order.class');

        $customers = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from($customer, 'c')
            ->leftJoin($order, 'o', Join::WITH, 'o.customer = c')
            ->where('c.createdAt < :before')
            ->andWhere('o.state = :state OR o IS NULL')
            ->setParameter('before', $form->getData()['anonymize_customer_without_any_orders_before_date'])
            ->setParameter('state', OrderInterface::STATE_CART)
            ->getQuery()
            ->execute();

        $this->removeNoneEligibleCustomers($customers);

        $this->anonymizerProcessor->anonymizeEntities($customers);
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
