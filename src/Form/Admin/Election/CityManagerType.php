<?php

namespace AppBundle\Form\Admin\Election;

use AppBundle\Address\Address;
use AppBundle\Entity\Election\CityManager;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityManagerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('phone', PhoneNumberType::class, [
                'label' => 'Téléphone',
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'default_region' => Address::FRANCE,
                'preferred_country_choices' => [Address::FRANCE],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CityManager::class);
    }
}
