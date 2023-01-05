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
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AnonymizerProcessor $anonymizerProcessor,
    ) {
    }

    /** @inheritdoc */
    public function process(string $formTypeClass, FormInterface $form): void
    {
        /** @var array $formData */
        $formData = $form->getData();

        /** @var \DateTime $before */
        $before = $formData['before_date'];
        $beforeClean = new \DateTime($before->format('Y-m-d'));

        $shopUsers = $this->entityManager
            ->createQueryBuilder()
            ->select('su')
            ->from(ShopUserInterface::class, 'su')
            ->where('su.lastLogin < :before')
            ->setParameter('before', $beforeClean)
            ->getQuery()
            ->execute()
        ;

        if (!is_array($shopUsers)) {
            throw new \LogicException('Error with query.');
        }

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
        return [\Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomerNotLoggedBeforeType::class];
    }
}
