<?php

namespace App\Form\Coalition;

use App\Form\EventCommandType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CoalitionEventType extends AbstractType
{
    public function getParent()
    {
        return EventCommandType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('coalition', EnabledCoalitionEntityType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->remove('capacity')
            ->remove('image')
        ;
    }
}
