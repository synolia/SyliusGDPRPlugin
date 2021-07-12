<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Controller;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Synolia\SyliusGDPRPlugin\Enum\GDPRSerializationKeyEnum;

class ExportDataController extends AbstractController
{
    protected const FILE_NAME = 'export_data';

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var ParameterBagInterface */
    private $parameterBag;

    /** @var SerializerInterface */
    private $serializer;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ParameterBagInterface $parameterBag,
        SerializerInterface $serializer
    ) {
        $this->customerRepository = $customerRepository;
        $this->parameterBag = $parameterBag;
        $this->serializer = $serializer;
    }

    public function __invoke(string $id): Response
    {
        $customer = $this->customerRepository->find($id);
        if (!$customer instanceof CustomerInterface) {
            $this->parameterBag->set('error', 'sylius.ui.admin.synolia_gdpr.customer.not_found');

            return $this->redirectToRoute('sylius_admin_customer_index');
        }

        $formattedCustomerData = $this->serializer->serialize($customer, 'json', ['groups' => [GDPRSerializationKeyEnum::CUSTOMER_DATA]]);

        $formattedDate = (new \DateTime())->format('Y_m_d');
        $fileName = sprintf('%s_%s', self::FILE_NAME, $formattedDate);
        $response = new Response($formattedCustomerData);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName . '.json'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
