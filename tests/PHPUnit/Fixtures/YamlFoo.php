<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures;

class YamlFoo
{
    private int $id;

    public $email = '';

    public $value;

    public $prefix;

    public $prefixValue;

    public $nullValue;

    public $bar;

    public $dynamicValue;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): YamlFoo
    {
        $this->id = $id;

        return $this;
    }
}
