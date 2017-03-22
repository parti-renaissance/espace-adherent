<?php

namespace AppBundle\Form;

use AppBundle\Entity\ProcurationProxy;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcurationProxyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', GenderType::class)
            ->add('lastName', TextType::class)
            ->add('firstNames', TextType::class)
            ->add('country', UnitedNationsCountryType::class)
            ->add('postalCode', TextType::class, [
                'required' => false,
            ])
            ->add('city', HiddenType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('cityName', TextType::class, [
                'required' => false,
            ])
            ->add('address', TextType::class)
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
            ->add('emailAddress', EmailType::class, [
                'empty_data' => '',
            ])
            ->add('birthdate', BirthdayType::class, [
                'widget' => 'choice',
                'years' => $options['years'],
                'placeholder' => [
                    'year' => 'AAAA',
                    'month' => 'MM',
                    'day' => 'JJ',
                ],
            ])
            ->add('voteCountry', UnitedNationsCountryType::class)
            ->add('votePostalCode', TextType::class, [
                'required' => false,
            ])
            ->add('voteCity', HiddenType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('voteCityName', TextType::class, [
                'required' => false,
            ])
            ->add('voteOffice', TextType::class, [
                'required' => false,
            ])
            ->add('electionPresidentialFirstRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionPresidentialSecondRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionLegislativeFirstRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionLegislativeSecondRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionLegislativeSecondRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('conditions', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $years = range((int) date('Y') - 17, (int) date('Y') - 120);

        $resolver->setDefaults([
            'translation_domain' => false,
            'data_class' => ProcurationProxy::class,
            'years' => array_combine($years, $years),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_procuration_proposal';
    }
}
