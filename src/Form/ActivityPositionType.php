<?php

namespace App\Form;

use App\Membership\ActivityPositions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityPositionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'choices' => ActivityPositions::CHOICES,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
