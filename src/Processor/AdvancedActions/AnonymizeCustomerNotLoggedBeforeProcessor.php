<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor\AdvancedActions;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\Form\FormInterface;
use Synolia\SyliusGDPRPlugin\Processor\AnonymizerProcessor;

class AnonymizeCustomerNotLoggedBeforeProcessor implements AdvancedActionsFormDataProcessorInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AnonymizerProcessor */
    private $anonymizerProcessor;

    public function __construct(EntityManagerInterface $entityManager, AnonymizerProcessor $anonymizerProcessor)
    {
        $this->entityManager = $entityManager;
        $this->anonymizerProcessor = $anonymizerProcessor;
    }

    /** {@inheritdoc} */
    public function process(string $formTypeClass, FormInterface $form): void
    {
        /** @var \DateTime $before */
        $before = $form->getData()['before_date'];
        $beforeClean = new \DateTime($before->format('Y-m-d'));

        $shopUsers = $this->entityManager
            ->createQueryBuilder()
            ->select('su')
            ->from(ShopUserInterface::class, 'su')
            ->where('su.lastLogin < :before')
            ->setParameter('before', $beforeClean)
            ->getQuery()
            ->execute();

        $this->anonymizerProcessor->anonymizeEntities($this->getCustomersFromShopUsers($shopUsers));
    }

    private function getCustomersFromShopUsers(array $shopUsers): array
    {
        $customers = [];

        /** @var ShopUserInterface $shopUser */
        foreach ($shopUsers as $shopUser) {
            $customer = $shopUser->getCustomer();
            if (!$customer instanceof CustomerInterface) {
                continue;
            }

            $customers[] = $customer;
        }

        return $customers;
    }

    public function getFormTypesClass(): array
    {
        return ['Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomerNotLoggedBeforeType'];
    }
}
