<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Validator;

use Faker\Factory;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FakerOptionsValidator
{
    public const DEFAULT_VALUE = 'NO_VALUE_PROVIDED';

    public string $faker;

    public array $args = [];

    public bool $unique = false;

    /** @var string|int|null */
    public $prefix = '';

    /** @var string|int|array|null */
    public $value;

    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'faker' => null,
            'args' => [],
            'unique' => false,
            'prefix' => '',
            'value' => self::DEFAULT_VALUE,
        ]);
        $resolver->addAllowedTypes('faker', ['null', 'string']);
        $resolver->addAllowedTypes('unique', 'boolean');
        $resolver->addAllowedTypes('args', 'array');
        $resolver->addAllowedTypes('prefix', ['string', 'null', 'integer']);
        $resolver->addAllowedTypes('value', ['string', 'null', 'integer', 'array', 'boolean']);

        $options = $resolver->resolve($options);
        if (null !== $options['faker']) {
            $resolver->addAllowedValues('faker', function ($value): bool {
                Factory::create()->getFormatter($value);

                return true;
            });
        }

        if (null !== $options['faker'] && self::DEFAULT_VALUE !== $options['value']) {
            throw new InvalidOptionsException('Attribute can\'t have a faker and a value property in the same time.');
        }

        $this->faker = $options['faker'];
        $this->args = $options['args'];
        $this->unique = $options['unique'];
        $this->prefix = $options['prefix'];
        $this->value = $options['value'];
    }
}
