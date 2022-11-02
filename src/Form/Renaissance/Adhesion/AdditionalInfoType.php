<?php

namespace App\Form\Renaissance\Adhesion;

use App\Address\Address;
use App\Entity\Adherent;
use App\Form\BirthdateType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdditionalInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fromCertifiedAdherent = $options['from_certified_adherent'];

        $builder
            ->add('birthdate', BirthdateType::class, [
                'disabled' => $fromCertifiedAdherent,
            ])
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'preferred_country_choices' => [Address::FRANCE],
            ])
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
        $resolver
            ->setDefaults([
                'data_class' => Adherent::class,
                'validation_groups' => ['additional_info'],
                'from_certified_adherent' => false,
            ])
            ->setAllowedTypes('from_certified_adherent', 'bool')
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_renaissance_membership';
    }
}
