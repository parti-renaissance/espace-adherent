<?php

namespace App\Form;

use App\ValueObject\Genders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenderType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => Genders::CHOICES,
            'translation_domain' => 'messages',
            'placeholder' => 'common.i.am',
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
