<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor\AdvancedActions;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Synolia\SyliusGDPRPlugin\Processor\AnonymizerProcessor;

class AnonymizeCustomersNotLoggedBeforeProcessor implements AdvancedActionsFormDataProcessorInterface
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
        /** @var string $shopUser */
        $shopUser = $this->parameterBag->get('sylius.model.shop_user.class');

        $shopUsers = $this->entityManager
            ->createQueryBuilder()
            ->select('su')
            ->from($shopUser, 'su')
            ->where('su.lastLogin < :before')
            ->setParameter('before', $form->getData()['anonymize_customers_not_logged_before_date'])
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
        return ['Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomersNotLoggedBeforeType'];
    }
}