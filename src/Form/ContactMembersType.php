<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactMembersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Entrez l\'objet de votre message'],
            ])
            ->add('message', TextareaType::class, [
                'attr' => ['placeholder' => 'Écrivez votre message'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('csrf_token_id', 'committee.contact_members')
            ->setDefault('csrf_field_name', 'token')
            ->setDefault('allow_extra_fields', true)
        ;
    }

    public function getBlockPrefix(): string
    {
        // CSRF token is checked by the controller and must be at root
        return '';
    }
}
