<?php

namespace App\Form;

use App\Entity\MemberSummary\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LanguageLevelChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => Language::LEVEL_CHOICES,
            ])
        ;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
