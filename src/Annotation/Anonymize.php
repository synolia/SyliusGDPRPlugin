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
    /**
     * @var string
     */
    public $faker;

    /**
     * @var array
     */
    public $args = [];

    /**
     * @var bool
     */
    public $unique = false;

    public function __construct(array $options = [])
    {
        if (isset($options['value'])) {
            $options['faker'] = $options['value'];
            unset($options['value']);
        }
        $anonymize = new FakerOptionsValidator($options);
        $this->faker = $anonymize->faker;
        $this->args = $anonymize->args;
        $this->unique = $anonymize->unique;
    }
}
