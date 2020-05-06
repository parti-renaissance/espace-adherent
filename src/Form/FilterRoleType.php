<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterRoleType extends AbstractType
{
    public const ROLES = [
        'CommitteeHosts',
        'CommitteeSupervisors',
        'CitizenProjectHosts',
    ];

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => self::ROLES,
            'choice_label' => function (string $role) {
                return "adherent_message.filter.role.$role";
            },
            'expanded' => false,
            'multiple' => true,
            'mapped' => false,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
