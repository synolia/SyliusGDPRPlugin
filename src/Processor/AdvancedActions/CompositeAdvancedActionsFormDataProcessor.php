<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor\AdvancedActions;

use Laminas\Stdlib\PriorityQueue;
use Symfony\Component\Form\FormInterface;

class CompositeAdvancedActionsFormDataProcessor implements AdvancedActionsFormDataProcessorInterface
{
    /**
     * @var PriorityQueue|AdvancedActionsFormDataProcessorInterface[]
     * @psalm-var PriorityQueue<AdvancedActionsFormDataProcessorInterface>
     */
    private readonly \Laminas\Stdlib\PriorityQueue $advancedActionsFormDataProcessor;

    public function __construct()
    {
        $this->advancedActionsFormDataProcessor = new PriorityQueue();
    }

    public function addProcessor(AdvancedActionsFormDataProcessorInterface $orderProcessor, int $priority = 0): void
    {
        $this->advancedActionsFormDataProcessor->insert($orderProcessor, $priority);
    }

    public function process(string $formTypeClass, FormInterface $form): void
    {
        foreach ($this->advancedActionsFormDataProcessor as $advancedActionsFormDataProcessor) {
            if (!in_array($formTypeClass, $advancedActionsFormDataProcessor->getFormTypesClass(), true)) {
                continue;
            }

            $advancedActionsFormDataProcessor->process($formTypeClass, $form);
        }
    }

    public function getFormTypesClass(): array
    {
        return [];
    }
}
