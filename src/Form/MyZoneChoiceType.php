<?php

namespace App\Form;

use App\Entity\Geo\Zone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyZoneChoiceType extends AbstractConnectedUserFormType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->getReferentZones(),
            'class' => Zone::class,
            'choice_label' => 'nameCode',
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
