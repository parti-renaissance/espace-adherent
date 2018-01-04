<?php

namespace AppBundle\Form;

use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\ThemeMeasure;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimelineThemeMeasureType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ThemeMeasure::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('measure', EntityType::class, [
                'label' => 'Mesure',
                'class' => Measure::class,
            ])
            ->add('featured', CheckboxType::class, [
                'label' => 'Mise en avant',
                'required' => false,
            ])
        ;
    }
}
