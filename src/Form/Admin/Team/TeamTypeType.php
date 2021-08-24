<?php

namespace App\Form\Admin\Team;

use App\Team\TypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices' => TypeEnum::ALL,
                'choice_label' => function (string $choice) {
                    return "team.type.$choice";
                },
            ]
        );
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
