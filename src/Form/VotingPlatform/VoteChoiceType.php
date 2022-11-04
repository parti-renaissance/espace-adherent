<?php

namespace App\Form\VotingPlatform;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoteChoiceType extends AbstractType
{
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'expanded' => true,
            'multiple' => false,
            'label' => false,
            'choice_label' => false,
            'required' => false,
            'placeholder' => false,
        ]);
    }
}
