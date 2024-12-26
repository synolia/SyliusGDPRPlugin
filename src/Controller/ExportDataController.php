<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Controller;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Synolia\SyliusGDPRPlugin\Enum\GDPRSerializationKeyEnum;
use Synolia\SyliusGDPRPlugin\Event\BeforeExportCustomerData;

#[AsController]
class ExportDataController extends AbstractController
{
    protected const FILE_NAME = 'export_data';

    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ParameterBagInterface $parameterBag,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/customers/{id}/export-data', name: 'synolia_sylius_gdpr_admin_export_customer_data', defaults: ['_sylius' => ['permission' => true, 'section' => 'admin', 'alias' => 'plugin_synolia_gdpr']], methods: ['GET|POST'])]
    public function __invoke(string $id): Response
    {
        $customer = $this->customerRepository->find($id);
        if (!$customer instanceof CustomerInterface) {
            $this->parameterBag->set('error', 'sylius.ui.admin.synolia_gdpr.customer.not_found');

            return $this->redirectToRoute('sylius_admin_customer_index');
        }

        $this->eventDispatcher->dispatch(new BeforeExportCustomerData($customer));

        $formattedCustomerData = $this->serializer->serialize($customer, 'json', ['groups' => [GDPRSerializationKeyEnum::CUSTOMER_DATA]]);

        $formattedDate = (new \DateTime())->format('Y_m_d');
        $fileName = sprintf('%s_%s', self::FILE_NAME, $formattedDate);
        $response = new Response($formattedCustomerData);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName . '.json',
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
