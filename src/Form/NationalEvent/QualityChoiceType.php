<?php

namespace App\Form\NationalEvent;

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
            'choices' => [
                'Colistier(e) Besoin d\'Europe' => 'colistier',
                'Parlementaire' => 'parlementaire',
                'Élu(e) local(e)' => 'elu_local',
            ],
            'expanded' => true,
            'multiple' => true,
        ]);
    }
}
