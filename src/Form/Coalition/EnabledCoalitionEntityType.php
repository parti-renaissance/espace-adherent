<?php

namespace App\Form\Coalition;

use App\Entity\Coalition\Coalition;
use App\Repository\Coalition\CoalitionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnabledCoalitionEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Coalition::class,
            'choice_label' => 'name',
            'query_builder' => function (CoalitionRepository $cr) {
                return $cr->findEnabled();
            },
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
