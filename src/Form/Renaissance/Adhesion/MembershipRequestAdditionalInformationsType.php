<?php

namespace App\Form\Renaissance\Adhesion;

use App\Address\Address;
use App\Form\ActivityPositionType;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembershipRequestAdditionalInformationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'preferred_country_choices' => [Address::FRANCE],
            ])
            ->add('position', ActivityPositionType::class, [
                'placeholder' => 'Catégorie socio-professionelle',
                'required' => false,
            ])
            ->add('fill_additional_informations', SubmitType::class, ['label' => 'Étape suivante'])
        ;

        // Use address country for phone by default
        $builder->get('phone')->get('country')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $formEvent) {
            if (!$formEvent->getData()) {
                $formEvent->setData(Address::FRANCE);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RenaissanceMembershipRequest::class,
            'validation_groups' => ['membership_request_additional_informations'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_renaissance_membership';
    }
}
