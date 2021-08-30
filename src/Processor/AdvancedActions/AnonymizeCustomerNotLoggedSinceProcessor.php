<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor\AdvancedActions;

use Symfony\Component\Form\FormInterface;

class AnonymizeCustomerNotLoggedSinceProcessor implements AdvancedActionsFormDataProcessorInterface
{
    public function process(string $formTypeClass, FormInterface $form): void
    {
        //TODO to implement
    }

    public function getFormTypesClass(): array
    {
        return ['Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomerNotLoggedSinceType'];
    }
}
