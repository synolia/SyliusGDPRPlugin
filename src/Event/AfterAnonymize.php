<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AfterAnonymize extends Event
{
    private object $entity;

    private array $oldData;

    public function __construct(Object $entity, array $oldData = [])
    {
        $this->entity = $entity;
        $this->oldData = $oldData;
    }

    public function getEntity(): Object
    {
        return $this->entity;
    }

    public function getOldData(): array
    {
        return $this->oldData;
    }
}
