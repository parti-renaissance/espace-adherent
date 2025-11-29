<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', EmailType::class)
            ->add('_password', PasswordType::class)
        ;

        if ($options['remember_me']) {
            $builder->add('_remember_me', CheckboxType::class, ['required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'remember_me' => false,
                'data_class' => null,
                'translation_domain' => false,
                'csrf_field_name' => '_csrf_token',
                'csrf_token_id' => 'authenticate',
            ])
            ->setAllowedTypes('remember_me', 'bool')
        ;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
