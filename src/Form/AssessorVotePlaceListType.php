<?php

namespace App\Form;

use App\Validator\UniqueVotePlaceAssessor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssessorVotePlaceListType extends AbstractType
{
    public function getParent()
    {
        return CollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => AssessorRoleAssociationType::class,
            'entry_options' => [
               'label' => false,
            ],
            'constraints' => [new UniqueVotePlaceAssessor()],
        ]);
    }
}
