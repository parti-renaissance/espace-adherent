<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactMembersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('csrf_token_id', 'committee.contact_members')
            ->setDefault('csrf_field_name', 'token')
            ->setDefault('allow_extra_fields', true)
        ;
    }

    public function getBlockPrefix()
    {
        // CSRF token is checked by the controller and must be at root
        return;
    }
}
