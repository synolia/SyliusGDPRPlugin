<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeAnonymize extends Event
{
    /** @var object */
    private $entity;

    public function __construct(Object $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): Object
    {
        return $this->entity;
    }
}
