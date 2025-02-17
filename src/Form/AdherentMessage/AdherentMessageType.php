<?php

namespace App\Form\AdherentMessage;

use App\AdherentMessage\AdherentMessageDataObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class)
            ->add('subject', TextType::class)
            ->add('content', TextareaType::class, [
                'attr' => [
                    'maxlength' => 6000,
                ],
                'with_character_count' => true,
            ])
            ->add('save', SubmitType::class)
            ->add('next', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AdherentMessageDataObject::class,
        ]);
    }
}
