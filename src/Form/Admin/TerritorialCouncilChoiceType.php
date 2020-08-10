<?php

namespace App\Form\Admin;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TerritorialCouncilChoiceType extends AbstractType
{
    public function getParent()
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'class' => TerritorialCouncil::class,
        ]);
    }
}
