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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Synolia\SyliusGDPRPlugin\Enum\GDPRSerializationKeyEnum;
use Synolia\SyliusGDPRPlugin\Event\BeforeExportCustomerData;

class ExportDataController extends AbstractController
{
    protected const FILE_NAME = 'export_data';

    private CustomerRepositoryInterface $customerRepository;

    private ParameterBagInterface $parameterBag;

    private EventDispatcherInterface $eventDispatcher;

    private SerializerInterface $serializer;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ParameterBagInterface $parameterBag,
        EventDispatcherInterface $eventDispatcher,
        SerializerInterface $serializer
    ) {
        $this->customerRepository = $customerRepository;
        $this->parameterBag = $parameterBag;
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
    }

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
            $fileName . '.json'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
