<?php

namespace App\Form\Renaissance\Adherent\Contribution;

use App\Adherent\Contribution\ContributionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RevenueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('revenueAmount', NumberType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ContributionRequest::class,
                'validation_groups' => ['fill_revenue'],
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_renaissance_contribution';
    }
}
