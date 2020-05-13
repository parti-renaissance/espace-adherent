<?php

namespace App\Form;

use App\BoardMember\BoardMemberMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoardMemberMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Entrez l\'objet de votre message'],
            ])
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Écrivez votre message'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', BoardMemberMessage::class);
    }
}
