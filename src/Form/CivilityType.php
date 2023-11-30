<?php

namespace App\Form;

use App\ValueObject\Genders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CivilityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => Genders::CIVILITY_CHOICES,
            'translation_domain' => 'messages',
            'expanded' => true,
            'invalid_message' => 'common.gender.invalid_choice',
        ]);
    }

    public function getParent()
    {
        return ReChoiceTabType::class;
    }
}
