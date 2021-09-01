<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Form\Type\Actions;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnonymizeCustomersNotLoggedBeforeType extends AbstractType
{
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('anonymize_customers_not_logged_before_date', DateType::class, [
                'label' => false,
                'widget' => 'single_text',
                'row_attr' => ['class' => 'ui field'],
            ])
            ->add('anonymize_customers_not_logged_submit', SubmitType::class, [
                'label' => 'sylius.ui.execute',
                'attr' => ['class' => 'ui blue button'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', 'sylius.ui.admin.synolia_gdpr.advanced_actions.anonymize_customers_not_logged_before.label');
    }
}
