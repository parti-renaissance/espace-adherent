<?php

namespace AppBundle\Form;

use AppBundle\Membership\Mandates;
use AppBundle\Membership\MembershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;

class AdherentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countryCode = $builder->getData() && $builder->getData()->getAddress() ? $builder->getData()->getAddress()->getCountry() : null;

        $builder
            ->add('firstName', TextType::class, [
                'format_identity_case' => true,
            ])
            ->add('lastName', TextType::class, [
                'format_identity_case' => true,
            ])
            ->add('nationality', CountryType::class, [
                'placeholder' => 'Nationalité',
            ])
            ->add('emailAddress', EmailType::class)
            ->add('position', ActivityPositionType::class, [
                'required' => false,
                'placeholder' => 'common.i.am',
            ])
            ->add('gender', GenderType::class)
            ->add('customGender', TextType::class, [
                'required' => false,
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
            ->add('address', AddressType::class)
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'preferred_country_choices' => $countryCode ? [$countryCode] : [],
            ])
            ->add('mandates', ChoiceType::class, [
                'label' => 'adherent.mandate.label',
                'choices' => Mandates::CHOICES,
                'required' => false,
                'multiple' => true,
            ])
            ->add('elected', CheckboxType::class, [
                'required' => false,
                'label' => 'adherent.form.elected',
            ])
        ;

        // Use address country for phone by default
        $builder->get('phone')->get('country')
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $formEvent) use ($countryCode) {
                if ($countryCode && !$formEvent->getData()) {
                    $formEvent->setData($countryCode);
                }
            })
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (!\array_key_exists('elected', $data)
                || (\array_key_exists('elected', $data) && false === $data['elected'])) {
                unset($data['mandates']);
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $years = range((int) date('Y') - 15, (int) date('Y') - 120);

        $resolver->setDefaults([
            'data_class' => MembershipRequest::class,
            'years' => array_combine($years, $years),
            'validation_groups' => ['Update'],
        ]);
    }
}
