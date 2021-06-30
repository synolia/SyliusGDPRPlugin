<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures;

use Synolia\SyliusGDPRPlugin\Annotation\Anonymize;

class Foo
{
    /**
     * @var string
     * @Anonymize("email")
     */
    public $email;

    /** @var string */
    public $bar;
}
