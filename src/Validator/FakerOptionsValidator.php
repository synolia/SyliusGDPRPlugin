<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Validator;

use Faker\Factory;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FakerOptionsValidator
{
    /** @var string */
    public $faker;

    /** @var array */
    public $args = [];

    /** @var bool */
    public $unique = false;

    public function __construct(array $options = [])
    {
        if ('' === $options['faker'] || null === $options['faker']) {
            throw new \LogicException('Faker option can\'t be null or \'\'.');
        }
        $resolver = new OptionsResolver();
        $resolver->setRequired('faker');
        $resolver->setDefaults([
            'args' => [],
            'unique' => false,
        ]);
        $resolver->addAllowedTypes('unique', 'boolean');
        $resolver->addAllowedTypes('args', 'array');
        $resolver->addAllowedValues('faker', function ($value): bool {
            Factory::create()->getFormatter($value);

            return true;
        });
        $options = $resolver->resolve($options);
        $this->faker = $options['faker'];
        $this->args = $options['args'];
        $this->unique = $options['unique'];
    }
}
