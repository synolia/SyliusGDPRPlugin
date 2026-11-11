<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Fixtures;

class TypedFoo
{
    public $untyped;

    public ?int $nullableInt = null;

    public int|string $union = 0;

    public \DateTime $date;

    public ?\DateTimeInterface $nullableDate = null;

    public bool $bool = false;

    public float $float = 0.0;

    public array $array = [];

    /** @var string[] */
    public $docBlockArray;

    public ?string $nullableString = null;
}
