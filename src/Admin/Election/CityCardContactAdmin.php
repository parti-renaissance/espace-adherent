<?php

namespace AppBundle\Admin\Election;

use AppBundle\Address\Address;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CityCardContactAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('phone', PhoneNumberType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'default_region' => Address::FRANCE,
                'preferred_country_choices' => [Address::FRANCE],
            ])
            ->add('caller', TextType::class, [
                'label' => 'Qui appelle?',
                'required' => false,
            ])
            ->add('done', CheckboxType::class, [
                'label' => 'Fait',
                'required' => false,
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
            ])
        ;
    }
}
