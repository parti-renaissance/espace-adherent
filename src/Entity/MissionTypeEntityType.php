<?php

namespace App\Entity;

use App\Entity\MemberSummary\MissionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionTypeEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => MissionType::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
            ])
        ;
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
