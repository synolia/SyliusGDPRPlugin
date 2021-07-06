<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ExportDataController extends AbstractController
{
    public function __invoke(string $id): Response
    {
        return $this->redirectToRoute('sylius_admin_customer_show', ['id' => $id]);
    }
}
