<?php

namespace App\Form;

use App\Summary\Contract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => Contract::CHOICES,
            ])
        ;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
