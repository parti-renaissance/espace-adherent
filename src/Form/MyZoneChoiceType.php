<?php

namespace App\Form;

use App\Entity\ReferentTag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyZoneChoiceType extends AbstractConnectedUserFormType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->getZones(),
            'class' => ReferentTag::class,
            'choice_label' => 'name',
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }

    private function getZones(): array
    {
        if (!$user = $this->getUser()) {
            return [];
        }

        if (!$user->isReferent()) {
            return [];
        }

        if (!$managedArea = $user->getManagedArea()) {
            return [];
        }

        return $managedArea->getZones()->toArray();
    }
}
