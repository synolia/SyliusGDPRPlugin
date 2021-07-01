<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Annotation;

use Doctrine\ORM\Mapping\Annotation;
use Synolia\SyliusGDPRPlugin\Validator\FakerOptionsValidator;

/**
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 */
final class Anonymize implements Annotation
{
    /** @var string */
    public $faker;

    /** @var array */
    public $args = [];

    /** @var bool */
    public $unique = false;

    /** @var string|int|null */
    public $prefix = '';

    /** @var string|int|array|bool|null */
    public $value;

    public function __construct(array $options = [])
    {
        $anonymize = new FakerOptionsValidator($options);
        $this->faker = $anonymize->faker;
        $this->args = $anonymize->args;
        $this->unique = $anonymize->unique;
        $this->prefix = $anonymize->prefix;
        $this->value = $anonymize->value;
    }
}
