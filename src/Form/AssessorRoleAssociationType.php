<?php

namespace App\Form;

use App\Assessor\AssessorRoleAssociationValueObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssessorRoleAssociationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('votePlace', AssociationVotePlaceType::class)
            ->add('adherent', AdherentEmailType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'E-mail de l\'adhérent',
                    'class' => 'form--full',
                ],
                'invalid_message' => 'assessor.adherent_association.adherent_not_found',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AssessorRoleAssociationValueObject::class,
        ]);
    }
}
