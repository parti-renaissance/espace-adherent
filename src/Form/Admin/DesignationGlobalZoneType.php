<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\VotingPlatform\Designation\DesignationGlobalZoneEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DesignationGlobalZoneType extends AbstractType
{
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => array_combine(DesignationGlobalZoneEnum::toArray(), DesignationGlobalZoneEnum::toArray()),
            'choice_label' => static function (string $type) {
                return 'voting_platform.designation.zone_'.strtolower($type);
            },
        ]);
    }
}
