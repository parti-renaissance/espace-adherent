<?php

declare(strict_types=1);

namespace App\Form;

use App\ValueObject\Genders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenderCivilityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => Genders::CIVILITY_CHOICES,
            'translation_domain' => 'messages',
            'expanded' => true,
            'invalid_message' => 'common.gender.invalid_choice',
        ]);
    }

    public function getParent(): string
    {
        return ReChoiceTabType::class;
    }
}
