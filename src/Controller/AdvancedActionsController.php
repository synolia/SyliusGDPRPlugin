<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;
use Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomerNotLoggedBeforeType;
use Synolia\SyliusGDPRPlugin\Processor\AdvancedActions\CompositeAdvancedActionsFormDataProcessor;

class AdvancedActionsController extends AbstractController
{
    private const FORMS = [
        AnonymizeCustomerNotLoggedBeforeType::class,
    ];

    /** @var CompositeAdvancedActionsFormDataProcessor */
    private $compositeAdvancedActionsFormDataProcessor;

    /** @var array */
    private $formsType;

    public function __construct(CompositeAdvancedActionsFormDataProcessor $compositeAdvancedActionsFormDataProcessor, array $formsType = [])
    {
        $this->compositeAdvancedActionsFormDataProcessor = $compositeAdvancedActionsFormDataProcessor;
        $this->formsType = $formsType;
    }

    public function __invoke(Request $request): Response
    {
        $formViews = array_merge(
            $this->generateAndProcessFormsType($request, self::FORMS),
            $this->generateAndProcessFormsType($request, $this->formsType
        ));

        return $this->render('@SynoliaSyliusGDPRPlugin\Gdpr\Actions.html.twig', ['forms' => $formViews]);
    }

    private function generateAndProcessFormsType(Request $request, array $formsType): array
    {
        $formTypeViews = [];
        foreach ($formsType as $formType) {
            $form = $this->createForm($formType);
            $form->handleRequest($request);

            /** @var string $classNameToClean */
            $classNameToClean = strrchr($formType, '\\');
            /** @var string $className */
            $className = substr($classNameToClean, 1);
            $formTypeViews[u($className)->snake()->toString()] = $form->createView();
            if ($form->isSubmitted() && $form->isValid()) {
                $this->compositeAdvancedActionsFormDataProcessor->process($formType, $form);
            }
        }

        return $formTypeViews;
    }
}
