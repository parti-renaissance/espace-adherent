<?php

namespace App\Form\NationalEvent;

use App\Entity\Adherent;
use App\Form\AcceptPersonalDataCollectType;
use App\Form\BirthdateType;
use App\Form\GenderCivilityType;
use App\Form\TelNumberType;
use App\NationalEvent\DTO\InscriptionRequest;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommonEventInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Adherent|null $adherent */
        $adherent = $options['adherent'];
        $isAdherent = $adherent instanceof Adherent;

        $builder
            ->add('email', EmailType::class, ['disabled' => $isAdherent])
            ->add('civility', GenderCivilityType::class, ['disabled' => $isAdherent && $adherent->getGender()])
            ->add('firstName', TextType::class, ['disabled' => $isAdherent && $adherent->getFirstName()])
            ->add('lastName', TextType::class, ['disabled' => $isAdherent && $adherent->getLastName()])
            ->add('birthPlace', TextType::class)
            ->add('birthdate', BirthdateType::class, [
                'years' => array_combine($years = range(date('Y') - 1, date('Y') - 120), $years),
                'disabled' => $isAdherent && $adherent->getBirthDate(),
            ])
            ->add('phone', TelNumberType::class, [
                'required' => false,
                'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT,
            ])
            ->add('postalCode', TextType::class, ['disabled' => $isAdherent && $adherent->getPostalCode()])
            ->add('isJAM', CheckboxType::class, ['required' => false])
            ->add('volunteer', CheckboxType::class, ['required' => false])
        ;

        if (false === $options['is_edit']) {
            $builder
                ->add('utmSource', HiddenType::class)
                ->add('utmCampaign', HiddenType::class)
                ->add('acceptCgu', AcceptPersonalDataCollectType::class)
                ->add('acceptMedia', AcceptPersonalDataCollectType::class)
                ->add('allowNotifications', CheckboxType::class, ['required' => false])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => InscriptionRequest::class,
                'adherent' => null,
                'is_edit' => false,
            ])
            ->setDefined(['adherent', 'is_edit'])
            ->addAllowedTypes('adherent', ['null', Adherent::class])
            ->addAllowedTypes('is_edit', ['bool'])
        ;
    }
}
