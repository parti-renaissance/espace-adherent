<?php

namespace App\Form;

use App\ValueObject\Genders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CivilityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => Genders::CIVILITY_CHOICES,
            'translation_domain' => 'messages',
            'expanded' => true,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
