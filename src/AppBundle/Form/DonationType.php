<?php

namespace AppBundle\Form;

use AppBundle\Intl\UnitedNationsBundle;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', NumberType::class, [
                'required' => true,
            ])
            ->add('gender', ChoiceType::class, [
                'required' => true,
                'expanded' => true,
                'choices' => [
                    'donation.gender.mister' => 'male',
                    'donation.gender.miss' => 'female',
                ],
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
            ])
            ->add('firstName', TextType::class, [
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->add('country', ChoiceType::class, [
                'required' => true,
                'choice_translation_domain' => false,
                'choices' => array_flip(UnitedNationsBundle::getCountries($options['locale'] ?? 'fr')),
            ])
            ->add('postalCode', TextType::class, [
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'required' => false,
            ])
            ->add('address', TextareaType::class, [
                'required' => false,
            ])
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'locale' => 'fr',
            'data_class' => 'AppBundle\Entity\Donation',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_donation';
    }
}
