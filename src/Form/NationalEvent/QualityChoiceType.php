<?php

declare(strict_types=1);

namespace App\Form\NationalEvent;

use App\NationalEvent\QualityEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QualityChoiceType extends AbstractType
{
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'choices' => array_combine(array_values(QualityEnum::LABELS), array_keys(QualityEnum::LABELS)),
            'expanded' => true,
            'multiple' => true,
        ]);
    }
}
