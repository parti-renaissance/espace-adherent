<?php

namespace AppBundle\Form;

use AppBundle\Committee\Event\CommitteeEventRegistrationCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeEventRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('emailAddress', EmailType::class)
            ->add('postalCode', TextType::class)
            ->add('newsletterSubscriber', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeEventRegistrationCommand::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'event_registration';
    }
}
