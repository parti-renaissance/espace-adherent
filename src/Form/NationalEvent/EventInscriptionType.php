<?php

namespace App\Form\NationalEvent;

use App\Event\Request\EventInscriptionRequest;
use App\Form\AcceptPersonalDataCollectType;
use App\Form\BirthdateType;
use App\Form\GenderCivilityType;
use App\Form\TelNumberType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('civility', GenderCivilityType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('birthPlace', TextType::class)
            ->add('birthdate', BirthdateType::class, ['years' => array_combine($years = range(date('Y') - 1, date('Y') - 120), $years)])
            ->add('phone', TelNumberType::class, [
                'required' => false,
                'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT,
            ])
            ->add('postalCode', TextType::class)
            ->add('acceptCgu', AcceptPersonalDataCollectType::class)
            ->add('acceptMedia', AcceptPersonalDataCollectType::class)
            ->add('allowNotifications', CheckboxType::class, ['required' => false])
            ->add('transportNeeds', CheckboxType::class, ['required' => false])
            ->add('volunteer', CheckboxType::class, ['required' => false])
            ->add('accessibility', TextType::class, ['required' => false])
            ->add('utmSource', HiddenType::class)
            ->add('utmCampaign', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventInscriptionRequest::class,
        ]);
    }
}
