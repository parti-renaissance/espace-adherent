<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class PotentialCoReferentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('referentPersonLinks', CollectionType::class, [
                'entry_type' => ReferentPersonLinkForCoReferentType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => 'checkbox',
                    ],
                ],
            ])
            ->add('save', SubmitType::class)
        ;
    }
}
