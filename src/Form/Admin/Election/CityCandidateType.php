<?php

namespace App\Form\Admin\Election;

use App\Address\AddressInterface;
use App\Entity\Election\CityCandidate;
use App\ValueObject\Genders;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityCandidateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('investitureType', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Type investiture',
                ],
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom',
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => Genders::CHOICES,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Sexe',
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Email',
                ],
            ])
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'default_region' => AddressInterface::FRANCE,
                'preferred_country_choices' => [AddressInterface::FRANCE],
                'attr' => [
                    'placeholder' => 'Téléphone',
                ],
            ])
            ->add('profile', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Profil',
                ],
            ])
            ->add('politicalScheme', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Schéma politique',
                ],
            ])
            ->add('alliances', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Orientation alliances',
                ],
            ])
            ->add('agreement', CheckboxType::class, [
                'label' => 'Accord',
                'required' => false,
            ])
            ->add('eligibleAdvisersCount', IntegerType::class, [
                'attr' => [
                    'placeholder' => 'Nombre de conseillers éligibles',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CityCandidate::class);
    }
}
