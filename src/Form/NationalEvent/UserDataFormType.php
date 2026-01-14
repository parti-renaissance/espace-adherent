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
use App\NationalEvent\NationalEventTypeEnum;
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
                'help' => 'Vous devez avoir minimum 16 ans le jour de l’événement.',
            ])
            ->add('phone', TelNumberType::class, [
                'required' => $event->isJEM(),
                'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT,
            ])
            ->add('postalCode', TextType::class, ['disabled' => $isAdherent && $adherent->getPostalCode()])
            ->add('accessibility', TextareaType::class, ['required' => false])
        ;

        if (NationalEventTypeEnum::DEFAULT === $event->type) {
            $builder
                ->add('transportNeeds', CheckboxType::class, ['required' => false])
                ->add('withChildren', CheckboxType::class, ['required' => false])
                ->add('children', TextareaType::class, ['required' => false])
                ->add('isResponsibilityWaived', CheckboxType::class, ['required' => false])
            ;
        }

        if (\in_array($event->type, [NationalEventTypeEnum::CAMPUS, NationalEventTypeEnum::DEFAULT], true)) {
            $builder
                ->add('volunteer', CheckboxType::class, ['required' => false])
                ->add('isJAM', CheckboxType::class, ['required' => false, 'label' => 'Je suis membre des Jeunes en marche'])
            ;
        }

        if (\in_array($event->type, [NationalEventTypeEnum::CAMPUS, NationalEventTypeEnum::DEFAULT, NationalEventTypeEnum::NRP], true)) {
            $builder
                ->add('birthPlace', TextType::class)
            ;

            if (false === $options['is_edit']) {
                $builder->add('allowNotifications', CheckboxType::class, ['required' => false]);
            }
        }

        if ($event->isJEM()) {
            $builder
                ->add('emergencyContactName', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Prénom Nom']])
                ->add('emergencyContactPhone', TelNumberType::class, ['label' => false, 'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT])
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
