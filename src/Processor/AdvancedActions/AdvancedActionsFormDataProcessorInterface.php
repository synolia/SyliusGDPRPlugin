<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor\AdvancedActions;

use Symfony\Component\Form\FormInterface;

interface AdvancedActionsFormDataProcessorInterface
{
    public function process(string $formTypeClass, FormInterface $form): void;

    public function getFormTypesClass(): array;
}
