<?php

namespace App\Form;

use App\Entity\Committee;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeChoiceType extends AbstractType
{
    public function getParent(): string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'class' => Committee::class,
                'choice_label' => 'name',
                'choice_value' => 'uuid',
            ])
        ;
    }
}
