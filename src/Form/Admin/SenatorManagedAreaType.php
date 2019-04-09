<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\ManagedArea\SenatorManagedArea;
use AppBundle\Entity\ReferentTag;
use Sonata\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SenatorManagedAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tag', EntityType::class, [
                'class' => ReferentTag::class,
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
                'data_class' => SenatorManagedArea::class,
            ])
        ;
    }
}
