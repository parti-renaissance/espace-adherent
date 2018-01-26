<?php

namespace AppBundle\Form;

use AppBundle\Membership\MembershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewMemberShipRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('address', AddressType::class)
            ->add('comEmail', CheckboxType::class, [
                'required' => false,
            ])
        ;

        if (in_array('Registration', $options['validation_groups'], true)) {
            $builder
                ->add('emailAddress', RepeatedType::class, [
                    'type' => EmailType::class,
                ])
                ->add('password', PasswordType::class)
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MembershipRequest::class,
            'translation_domain' => false,
            'validation_groups' => ['Registration'],
            'country_iso' => null,
        ]);
    }
}
