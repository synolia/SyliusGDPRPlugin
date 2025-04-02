<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Kernel;

class ExportDataControllerTest extends WebTestCase
{
    public function testExportDataWithCart(): void
    {
        if (Kernel::MAJOR_VERSION < 6) {
            $this->markTestSkipped('Test on for Symfony 6+');
        }

        $client = static::createClient();

        // login a user
        $shopUser = static::getContainer()->get('sylius.repository.shop_user')->findOneBy([]);
        $client->loginUser($shopUser);

        // go to product page
        $client->request('GET', '/en_US/products/solar-echo-t-shirt');
        $this->assertSelectorTextContains('h1', 'Solar Echo T-Shirt');

        // add product to cart
        $client->submitForm('Add to cart');

        $client->request('GET', '/en_US/cart/');

        $this->assertPageTitleContains('Your shopping cart');

        // login as admin and go to customer page
        $adminUser = static::getContainer()->get('sylius.repository.admin_user')->findOneBy([]);
        $client->loginUser($adminUser, 'admin');
        $client->request('GET', sprintf('/admin/customers/%s', $shopUser->getId()));

        // export data for this user
        $client->clickLink(self::getContainer()->get('translator')->trans('sylius.ui.admin.synolia_gdpr.customer.export_data'));
        $this->assertResponseIsSuccessful();
    }
}
