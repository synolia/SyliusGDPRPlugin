<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AfterAnonymize extends Event
{
    public function __construct(private readonly object $entity, private readonly array $oldData = [])
    {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getOldData(): array
    {
        return $this->oldData;
    }
}
