<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($options['username_parameter'], TextType::class)
            ->add($options['password_parameter'], PasswordType::class)
        ;

        if ($options['remember_me']) {
            $builder->add($options['remember_me_parameter'], CheckboxType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'username_parameter' => '_login_email',
                'password_parameter' => '_login_password',
                'remember_me_parameter' => '_remember_me',
                'csrf_field_name' => '_login_csrf',
                'csrf_token_id' => 'authenticate',
                'remember_me' => false,
                'data_class' => null,
                'translation_domain' => false,
            ])
            ->setAllowedTypes('username_parameter', 'string')
            ->setAllowedTypes('password_parameter', 'string')
            ->setAllowedTypes('remember_me_parameter', 'string')
            ->setAllowedTypes('remember_me', 'bool')
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_login';
    }
}
