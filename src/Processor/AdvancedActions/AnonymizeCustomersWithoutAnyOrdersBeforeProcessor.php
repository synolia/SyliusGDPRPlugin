<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor\AdvancedActions;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Form\FormInterface;
use Synolia\SyliusGDPRPlugin\Processor\AnonymizerProcessor;

class AnonymizeCustomersWithoutAnyOrdersBeforeProcessor implements AdvancedActionsFormDataProcessorInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AnonymizerProcessor */
    private $anonymizerProcessor;

    /** @var FactoryInterface */
    private $customerFactory;

    /** @var FactoryInterface */
    private $orderFactory;

    public function __construct(EntityManagerInterface $entityManager, AnonymizerProcessor $anonymizerProcessor, FactoryInterface $customerFactory, FactoryInterface $orderFactory)
    {
        $this->entityManager = $entityManager;
        $this->anonymizerProcessor = $anonymizerProcessor;
        $this->customerFactory = $customerFactory;
        $this->orderFactory = $orderFactory;
    }

    /** {@inheritdoc} */
    public function process(string $formTypeClass, FormInterface $form): void
    {
        $customers = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(get_class($this->customerFactory->createNew()), 'c')
            ->innerJoin(get_class($this->orderFactory->createNew()), 'o')
            ->where('c.createdAt < :before')
            ->andWhere('o.state = :state')
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
                if ($order->getState() === 'cart') {
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
