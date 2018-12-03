<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\CommunicationManagerArea;
use AppBundle\Entity\ReferentTag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommunicationManagerAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tags', EntityType::class, [
                'label' => 'referent_tags.label',
                'class' => ReferentTag::class,
                'required' => false,
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommunicationManagerArea::class,
        ]);
    }
}
