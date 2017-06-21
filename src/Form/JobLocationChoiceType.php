<?php

namespace AppBundle\Form;

use AppBundle\Summary\JobLocation;
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
                'cast_null_to_string' => true,
            ])
        ;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
