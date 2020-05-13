<?php

namespace App\Form;

use App\Deputy\DeputyMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeputyMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => false,
                'filter_emojis' => true,
            ])
            ->add('content', PurifiedTextareaType::class, [
                'label' => false,
                'attr' => [
                    'maxlength' => 6000,
                ],
                'filter_emojis' => true,
                'purifier_type' => 'enrich_content',
                'with_character_count' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', DeputyMessage::class);
    }
}
