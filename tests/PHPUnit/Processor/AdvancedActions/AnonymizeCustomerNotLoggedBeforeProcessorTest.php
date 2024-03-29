<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Processor\AdvancedActions;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Forms;
use Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomersNotLoggedBeforeType;
use Synolia\SyliusGDPRPlugin\Processor\AdvancedActions\CompositeAdvancedActionsFormDataProcessor;
use Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Processor\WithSessionTrait;

class AnonymizeCustomerNotLoggedBeforeProcessorTest extends KernelTestCase
{
    use WithSessionTrait;

    private ?EntityManagerInterface $manager = null;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->manager = static::getContainer()->get(EntityManagerInterface::class);
        $this->manager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->manager->rollback();

        parent::tearDown();
    }

    public function testAnonymizeCustomers(): void
    {
        $this->createSession();

        /** @var array<int, ShopUserInterface> $shopUsers */
        $shopUsers = static::getContainer()->get('sylius.repository.shop_user')->findAll();
        $shopUsers[0]->setLastLogin(new \DateTime());
        $shopUsers[1]->setLastLogin((new \Datetime())->sub(new \DateInterval('P2D')));

        $this->manager->flush();

        $form = Forms::createFormFactoryBuilder()->getFormFactory()->create(
            AnonymizeCustomersNotLoggedBeforeType::class,
            ['anonymize_customers_not_logged_before_date' => (new \Datetime())->sub(new \DateInterval('P1D'))],
        );

        /** @var CompositeAdvancedActionsFormDataProcessor $composite */
        $composite = static::getContainer()->get(CompositeAdvancedActionsFormDataProcessor::class);
        $composite->process(AnonymizeCustomersNotLoggedBeforeType::class, $form);

        $this->assertStringContainsString('anonymized-', $shopUsers[1]->getEmail());
        $this->assertStringNotContainsString('anonymized-', $shopUsers[0]->getEmail());
    }
}
