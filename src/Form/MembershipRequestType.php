<?php

namespace App\Form;

use App\Address\AddressInterface;
use App\Adhesion\MembershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembershipRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('civility', CivilityType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('nationality', CountryType::class, ['preferred_choices' => [AddressInterface::FRANCE]])
            ->add('address', AutocompleteAddressType::class, ['with_additional_address' => true])
            ->add('consentDataCollect', AcceptPersonalDataCollectType::class)
            ->add('utmSource', HiddenType::class)
            ->add('utmCampaign', HiddenType::class)
            ->add('exclusiveMembership', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Je certifie sur l’honneur que je n’appartiens à aucun autre parti politique' => true,
                    'J’appartiens déja à un parti politique' => false,
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
            ->add('allowNotifications', CheckboxType::class, ['required' => false])
            ->add('isPhysicalPerson', RequiredCheckboxType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MembershipRequest::class,
        ]);
    }
}
