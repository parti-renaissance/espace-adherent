<?php

namespace AppBundle\Form;

use AppBundle\Referent\ReferentMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Entrez l\'objet de votre message'],
                'filter_emojis' => true,
            ])
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'maxlength' => 5000,
                    'placeholder' => 'Écrivez votre message',
                ],
                'filter_emojis' => true,
                'purify_html' => true,
                'with_character_count' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ReferentMessage::class);
    }
}
