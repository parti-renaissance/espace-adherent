<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateMembershipRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('password')
            ->remove('conditions')
            ->remove($firstName = 'firstName')->add($firstName, TextType::class)
            ->remove($lastName = 'lastName')->add($lastName, TextType::class)
            ->remove($email = 'emailAddress')->add($email, EmailType::class)
        ;
    }

    public function getParent()
    {
        return MembershipRequestType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('validation_groups', ['Default']);
    }
}
