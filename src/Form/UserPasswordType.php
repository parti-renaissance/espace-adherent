<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserPasswordType extends AbstractType
{
    private EncoderFactoryInterface $encoders;

    public function __construct(EncoderFactoryInterface $encoders)
    {
        $this->encoders = $encoders;
    }

    public function getParent()
    {
        return PasswordType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $formEvent) use ($options) {
            $formEvent->setData($this->encoders->getEncoder($options['user_class'])->encodePassword($formEvent->getData(), null));
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('user_class');
    }
}
