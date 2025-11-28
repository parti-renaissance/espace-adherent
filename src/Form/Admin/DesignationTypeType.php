<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\VotingPlatform\Designation\DesignationTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DesignationTypeType extends AbstractType
{
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => array_combine(DesignationTypeEnum::MAIN_TYPES, DesignationTypeEnum::MAIN_TYPES),
            'choice_label' => static function (string $type) {
                return 'voting_platform.designation.type_'.$type;
            },
        ]);
    }
}
