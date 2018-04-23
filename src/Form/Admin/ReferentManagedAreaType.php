<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\ReferentManagedArea;
use AppBundle\Entity\ReferentTag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentManagedAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tags', EntityType::class, [
                'label' => 'referent.label.tags',
                'class' => ReferentTag::class,
                'required' => false,
                'multiple' => true,
            ])
            ->add('markerLatitude', TextType::class, [
                'label' => 'Latitude du point sur la carte des référents',
                'required' => false,
            ])
            ->add('markerLongitude', TextType::class, [
                'label' => 'Longitude du point sur la carte des référents',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReferentManagedArea::class,
        ]);
    }
}
