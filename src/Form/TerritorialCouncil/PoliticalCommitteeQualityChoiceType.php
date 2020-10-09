<?php

namespace App\Form\TerritorialCouncil;

use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PoliticalCommitteeQualityChoiceType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => TerritorialCouncilQualityEnum::ALL_POLITICAL_COMMITTEE_QUALITIES,
            'choice_label' => function (string $choice): string {
                return "political_committee.membership.quality.$choice";
            },
       ]);
    }
}
