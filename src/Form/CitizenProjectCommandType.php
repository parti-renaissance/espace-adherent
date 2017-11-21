<?php

namespace AppBundle\Form;

use AppBundle\CitizenProject\CitizenProjectCommand;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenProjectCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('description', TextareaType::class, [
                'filter_emojis' => true,
                'purify_html' => true,
            ])
            ->add('address', NullableAddressType::class, [
                'required' => false,
            ])
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CitizenProjectCommand::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'citizen_project';
    }
}
