<?php

namespace App\Form\NationalEvent;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class DefaultEventInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accessibility', TextType::class, ['required' => false])
            ->add('transportNeeds', CheckboxType::class, ['required' => false])
            ->add('withChildren', CheckboxType::class, ['required' => false])
            ->add('children', TextareaType::class, ['required' => false])
            ->add('isResponsibilityWaived', CheckboxType::class, ['required' => false])
        ;
    }

    public function getParent(): string
    {
        return CommonEventInscriptionType::class;
    }
}
