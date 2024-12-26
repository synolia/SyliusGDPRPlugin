<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\String\u;
use Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomersNotLoggedBeforeType;
use Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomersWithoutAnyOrdersBeforeType;
use Synolia\SyliusGDPRPlugin\Processor\AdvancedActions\CompositeAdvancedActionsFormDataProcessor;

#[AsController]
class AdvancedActionsController extends AbstractController
{
    private const FORMS = [
        AnonymizeCustomersNotLoggedBeforeType::class,
        AnonymizeCustomersWithoutAnyOrdersBeforeType::class,
    ];

    public function __construct(
        private readonly CompositeAdvancedActionsFormDataProcessor $compositeAdvancedActionsFormDataProcessor,
        private readonly array $formsType = [],
    ) {
    }

    #[Route('/gdpr/actions', name: 'synolia_sylius_gdpr_admin_advanced_actions', defaults: ['_sylius' => ['permission' => true, 'section' => 'admin', 'alias' => 'plugin_synolia_gdpr']])]
    public function __invoke(Request $request): Response
    {
        $formViews = array_merge(
            $this->generateAndProcessFormsType($request, self::FORMS),
            $this->generateAndProcessFormsType(
                $request,
                $this->formsType,
            ),
        );

        return $this->render('@SynoliaSyliusGDPRPlugin\Gdpr\Actions.html.twig', ['forms' => $formViews]);
    }

    private function generateAndProcessFormsType(Request $request, array $formsType): array
    {
        $formTypeViews = [];
        foreach ($formsType as $formType) {
            $form = $this->createForm($formType);
            $form->handleRequest($request);

            /** @var string $classNameToClean */
            $classNameToClean = strrchr((string) $formType, '\\');
            /** @var string $className */
            $className = substr($classNameToClean, 1);
            $formTypeViews[u($className)->snake()->toString()] = $form->createView();
            if ($form->isSubmitted() && $form->isValid()) {
                $this->compositeAdvancedActionsFormDataProcessor->process($formType, $form);

                $request->getSession()->getFlashBag()->add('success', 'sylius.ui.admin.synolia_gdpr.success');
            }
        }

        return $formTypeViews;
    }
}
