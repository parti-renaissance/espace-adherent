<?php

namespace App\Form\Renaissance\Adhesion;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdhesionAmountType extends AbstractType
{
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'required' => false,
           'placeholder' => false,
           'choices' => [
               'Tarif réduit 10€ *' => 10,
               'Tarif normal 30€' => 30,
           ],
           'expanded' => true,
       ]);
    }
}
