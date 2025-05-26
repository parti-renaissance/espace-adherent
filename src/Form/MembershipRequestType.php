<?php

namespace App\Form;

use App\Adhesion\Request\MembershipRequest;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembershipRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fromCertifiedAdherent = $options['from_certified_adherent'];

        $builder
            ->add('email', EmailType::class)
            ->add('civility', GenderCivilityType::class, ['disabled' => $fromCertifiedAdherent])
            ->add('firstName', TextType::class, ['disabled' => $fromCertifiedAdherent])
            ->add('lastName', TextType::class, ['disabled' => $fromCertifiedAdherent])
            ->add('nationality', ReCountryType::class, ['disabled' => $fromCertifiedAdherent])
            ->add('address', AutocompleteAddressType::class, ['with_additional_address' => true])
            ->add('consentDataCollect', AcceptPersonalDataCollectType::class)
            ->add('utmSource', HiddenType::class)
            ->add('utmCampaign', HiddenType::class)
            ->add('exclusiveMembership', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Je certifie sur l’honneur que je n’appartiens à aucun autre parti politique' => true,
                    'J’appartiens déjà à un parti politique' => false,
                ],
                'expanded' => true,
            ])
            ->add('partyMembership', ChoiceType::class, [
                'choices' => [
                    'Je suis membre de “Territoires de Progrès” et je peux bénéficier à ce titre de la double adhésion prévue dans les dispositions transitoires des status de Renaissance' => 1,
                    'Je suis membre de “Agir, la droite constructive” et je peux bénéficier à ce titre de la double adhésion prévue dans les dispositions transitoires des status de Renaissance' => 2,
                    'J’appartiens à un autre parti politique' => 3,
                ],
                'expanded' => true,
            ])
            ->add('phone', TelNumberType::class, [
                'required' => false,
                'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT,
            ])
            ->add('acceptSmsNotification', CheckboxType::class, ['required' => false, 'mapped' => false])
            ->add('allowNotifications', CheckboxType::class, ['required' => false])
            ->add('isPhysicalPerson', RequiredCheckboxType::class)
            ->add('amount', HiddenType::class, ['error_bubbling' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => MembershipRequest::class,
                'from_certified_adherent' => false,
            ])
            ->setAllowedTypes('from_certified_adherent', 'bool')
        ;
    }
}
