<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AfterAnonymize extends Event
{
    public function __construct(private Object $entity, private array $oldData = [])
    {
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
