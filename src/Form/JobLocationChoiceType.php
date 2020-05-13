<?php

namespace App\Form;

use App\Summary\JobLocation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobLocationChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => JobLocation::CHOICES,
                'expanded' => true,
                'multiple' => true,
            ])
        ;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
