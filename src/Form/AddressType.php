<?php

namespace App\Form;

use App\Address\Address;
use App\Intl\FranceCitiesBundle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class)
            ->add('city', HiddenType::class, [
                'required' => false,
                'error_bubbling' => $options['child_error_bubbling'],
                'disabled' => $options['disable_fields'],
            ])
            ->add('cityName', TextType::class, [
                'required' => false,
                'disabled' => $options['disable_fields'],
            ])
            ->add('country', UnitedNationsCountryType::class, [
                'disabled' => $options['disable_fields'],
                'placeholder' => 'Sélectionner un pays',
                'preferred_choices' => ['FR'],
            ])
        ;
        if ($options['set_address_region']) {
            $builder->add('region', TextType::class, [
                'required' => false,
                'disabled' => $options['disable_fields'],
            ]);
        }

        $field = $builder->create('postalCode', TextType::class, [
            'error_bubbling' => $options['child_error_bubbling'],
            'disabled' => $options['disable_fields'],
        ]);

        $field->addModelTransformer(new CallbackTransformer(
            function ($data) {
                return $data;
            },
            function ($value) {
                return str_replace(' ', '', $value);
            }
        ));

        $builder->add($field);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Address $address */
            $address = $event->getData();

            if ($address && $address->getCityName() && $address->getPostalCode() && Address::FRANCE === $address->getCountry()) {
                $inseeCode = FranceCitiesBundle::getCityInseeCode($address->getPostalCode(), $address->getCityName());

                if ($inseeCode) {
                    $address->setCity(sprintf('%s-%s', $address->getPostalCode(), $inseeCode));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Address::class,
                'error_bubbling' => false,
                'child_error_bubbling' => true,
                'disable_fields' => false,
                'set_address_region' => false,
            ])
            ->setAllowedTypes('disable_fields', 'bool')
            ->setAllowedTypes('child_error_bubbling', 'bool')
        ;
    }
}
