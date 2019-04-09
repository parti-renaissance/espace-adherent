<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\ManagedArea\EuropeanDeputyManagedArea;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EuropeanDeputyManagedAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isEuropeanDeputy', CheckboxType::class, [
                'required' => false,
            ])
            ->add('since', DatePickerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'required' => false,
                'data_class' => EuropeanDeputyManagedArea::class,
            ])
        ;
    }
}
