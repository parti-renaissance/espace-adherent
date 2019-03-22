<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\AdherentReferentData;
use AppBundle\Entity\ReferentTag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AdherentReferentData::class,
        ]);
    }
}
