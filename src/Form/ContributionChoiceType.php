<?php

namespace App\Form;

use App\Summary\Contribution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContributionChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => Contribution::CHOICES,
                'expanded' => true,
                'cast_null_to_string' => true,
            ])
        ;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
