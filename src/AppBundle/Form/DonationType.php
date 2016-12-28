<?php

namespace AppBundle\Form;

use AppBundle\Intl\UnitedNationsBundle;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
            ->add('amount', NumberType::class)
            ->add('gender', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'donation.gender.mister' => 'male',
                    'donation.gender.miss' => 'female',
                ],
            ])
            ->add('lastName', TextType::class)
            ->add('firstName', TextType::class)
            ->add('email', EmailType::class)
            ->add('country', ChoiceType::class, [
                'choice_translation_domain' => false,
                'choices' => array_flip(UnitedNationsBundle::getCountries($options['locale'] ?? 'fr')),
            ])
            ->add('postalCode', TextType::class)
            ->add('city', ChoiceType::class)
            ->add('address', TextType::class)
            ->add('phone', PhoneNumberType::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
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
