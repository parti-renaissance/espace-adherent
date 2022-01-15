<?php

namespace App\Form\AdherentMessage;

use App\AdherentMessage\AdherentMessageDataObject;
use App\Form\PurifiedTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class)
            ->add('subject', TextType::class)
            ->add('content', PurifiedTextareaType::class, [
                'attr' => [
                    'maxlength' => 6000,
                ],
                'purify_html_profile' => 'enrich_content',
                'with_character_count' => true,
            ])
            ->add('save', SubmitType::class)
            ->add('next', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdherentMessageDataObject::class,
        ]);
    }
}
