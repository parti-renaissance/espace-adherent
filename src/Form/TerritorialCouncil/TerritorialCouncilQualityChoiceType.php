<?php

namespace App\Form\TerritorialCouncil;

use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TerritorialCouncilQualityChoiceType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => TerritorialCouncilQualityEnum::ALL,
            'choice_label' => function (string $choice): string {
                return "territorial_council.membership.quality.$choice";
            },
       ]);
    }
}
