<?php

declare(strict_types=1);

namespace App\Form\NationalEvent;

use App\Entity\Adherent;
use App\Entity\NationalEvent\NationalEvent;
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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDataFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var NationalEvent $event */
        $event = $options['event'];
        /** @var Adherent|null $adherent */
        $adherent = $options['adherent'];
        $isAdherent = $adherent instanceof Adherent;

        $builder
            ->add('email', EmailType::class, ['disabled' => $isAdherent])
            ->add('civility', GenderCivilityType::class, ['disabled' => $isAdherent && $adherent->getGender()])
            ->add('firstName', TextType::class, ['disabled' => $isAdherent && $adherent->getFirstName()])
            ->add('lastName', TextType::class, ['disabled' => $isAdherent && $adherent->getLastName()])
            ->add('birthdate', BirthdateType::class, [
                'min_age' => 16,
                'reference_date' => $event->startDate,
                'disabled' => $isAdherent && $adherent->getBirthDate(),
                'help' => "Vous devez avoir minimum 16 ans le jour de l'événement.",
            ])
            ->add('phone', TelNumberType::class, [
                'required' => $event->phoneRequired,
                'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT,
            ])
            ->add('postalCode', TextType::class, ['disabled' => $isAdherent && $adherent->getPostalCode()])
        ;

        if ($event->showAccessibility) {
            $builder->add('accessibility', TextareaType::class, ['required' => $event->requiredAccessibility]);
        }

        if ($event->showTransportNeeds) {
            $builder->add('transportNeeds', CheckboxType::class, ['required' => false]);
        }

        if ($event->showWithChildren) {
            $builder
                ->add('withChildren', CheckboxType::class, ['required' => false])
                ->add('children', TextareaType::class, ['required' => false])
                ->add('isResponsibilityWaived', CheckboxType::class, ['required' => false])
            ;
        }

        if ($event->showVolunteer) {
            $builder->add('volunteer', CheckboxType::class, ['required' => false]);
        }

        if ($event->showIsJAM) {
            $builder->add('isJAM', CheckboxType::class, ['required' => false]);
        }

        if ($event->showBirthPlace) {
            $builder->add('birthPlace', TextType::class, ['required' => $event->requiredBirthPlace]);
        }

        if ($event->showAllowNotifications && false === $options['is_edit']) {
            $builder->add('allowNotifications', CheckboxType::class, ['required' => false]);
        }

        if ($event->showEmergencyContact) {
            $required = $event->requiredEmergencyContact;
            $builder
                ->add('emergencyContactName', TextType::class, ['label' => false, 'required' => $required, 'attr' => ['placeholder' => 'Prénom Nom']])
                ->add('emergencyContactPhone', TelNumberType::class, ['label' => false, 'required' => $required, 'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT])
            ;
        }

        if (false === $options['is_edit']) {
            $builder
                ->add('utmSource', HiddenType::class)
                ->add('utmCampaign', HiddenType::class)
                ->add('acceptCgu', AcceptPersonalDataCollectType::class)
                ->add('acceptMedia', AcceptPersonalDataCollectType::class)
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
            ->setDefined(['adherent', 'is_edit', 'event'])
            ->addAllowedTypes('adherent', ['null', Adherent::class])
            ->addAllowedTypes('is_edit', 'bool')
            ->addAllowedTypes('event', NationalEvent::class)
        ;
    }
}
