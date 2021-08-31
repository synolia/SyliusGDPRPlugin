<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Processor\AdvancedActions;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Forms;
use Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomerNotLoggedBeforeType;
use Synolia\SyliusGDPRPlugin\Processor\AdvancedActions\CompositeAdvancedActionsFormDataProcessor;

class AnonymizeCustomerNotLoggedBeforeProcessorTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->manager->beginTransaction();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->manager->rollback();
    }

    public function testAnonymizeCustomers(): void
    {
        /** @var array<int, ShopUserInterface> $shopUsers */
        $shopUsers = $this->getContainer()->get('sylius.repository.shop_user')->findAll();
        $shopUsers[0]->setLastLogin(new \DateTime());
        $shopUsers[1]->setLastLogin((new \Datetime())->sub(new \DateInterval('P2D')));

        $this->manager->flush();

        $form = Forms::createFormFactoryBuilder()->getFormFactory()->create(
            AnonymizeCustomerNotLoggedBeforeType::class,
            ['before_date' => (new \Datetime())->sub(new \DateInterval('P1D'))]
        );

        /** @var CompositeAdvancedActionsFormDataProcessor $composite */
        $composite = $this->getContainer()->get(CompositeAdvancedActionsFormDataProcessor::class);
        $composite->process(AnonymizeCustomerNotLoggedBeforeType::class, $form);

        $this->assertStringContainsString('anonymized-', $shopUsers[1]->getEmail());
        $this->assertStringNotContainsString('anonymized-', $shopUsers[0]->getEmail());
    }
}
