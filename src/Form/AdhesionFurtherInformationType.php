<?php

namespace App\Form;

use App\Address\AddressInterface;
use App\Adherent\MandateTypeEnum;
use App\Entity\Adherent;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdhesionFurtherInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mandates', AdherentMandateType::class, [
                'label' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('birthdate', BirthdateType::class, [])
//            ->add('refuseJamNotification', CheckboxType::class, ['required' => false])
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'preferred_country_choices' => [AddressInterface::FRANCE],
                'default_region' => AddressInterface::FRANCE,
                'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT,
            ])
//            ->add('acceptSmsNotification', CheckboxType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adherent::class,
        ]);
    }
}
