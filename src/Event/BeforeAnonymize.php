<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeAnonymize extends Event
{
    public function __construct(private Object $entity)
    {
    }

    public function getEntity(): Object
    {
        return $this->entity;
    }
}
