<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor\AdvancedActions;

use Symfony\Component\Form\FormInterface;
use Zend\Stdlib\PriorityQueue;

class CompositeAdvancedActionsFormDataProcessor implements AdvancedActionsFormDataProcessorInterface
{
    /**
     * @var PriorityQueue|AdvancedActionsFormDataProcessorInterface[]
     *
     * @psalm-var PriorityQueue<AdvancedActionsFormDataProcessorInterface>
     */
    private $advancedActionsFormDataProcessor;

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
