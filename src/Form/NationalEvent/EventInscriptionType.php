<?php

namespace App\Form\NationalEvent;

use App\Event\Request\EventInscriptionRequest;
use App\Form\AcceptPersonalDataCollectType;
use App\Form\BirthdateType;
use App\Form\CivilityType;
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
            ->add('civility', CivilityType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('birthdate', BirthdateType::class, ['years' => array_combine($years = range(date('Y') - 1, date('Y') - 120), $years)])
            ->add('phone', TelNumberType::class, [
                'required' => false,
                'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT,
            ])
            ->add('postalCode', TextType::class)
            ->add('acceptCgu', AcceptPersonalDataCollectType::class)
            ->add('acceptMedia', AcceptPersonalDataCollectType::class)
            ->add('allowNotifications', CheckboxType::class, ['required' => false])
            ->add('qualities', QualityChoiceType::class)
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
