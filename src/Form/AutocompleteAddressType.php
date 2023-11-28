<?php

namespace App\Form;

use App\Address\Address;
use App\Address\AddressInterface;
use App\FranceCities\FranceCities;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AutocompleteAddressType extends AbstractType
{
    public function __construct(private readonly FranceCities $franceCities)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('autocomplete', ReAutoCompleteType::class, [
                'mapped' => false,
                'required' => true,
            ])
            ->add('address', ReInputType::class, [
                'required' => false,
            ])
            ->add('cityName', ReInputType::class)
            ->add('country', CountryType::class, [
                'placeholder' => '',
                'preferred_choices' => [AddressInterface::FRANCE],
                'invalid_message' => 'common.country.invalid',
                'empty_data' => 'FR',
            ])
            ->add('postalCode', ReInputType::class)
        ;

        $builder
            ->get('postalCode')
            ->addModelTransformer(new CallbackTransformer(
                function ($data) {
                    return $data;
                },
                function ($value) {
                    return str_replace(' ', '', $value);
                }
            ))
        ;

        $builder
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Address $address */
                $address = $event->getData();

                if ($address && AddressInterface::FRANCE === $address->getCountry() && $address->getCityName() && $address->getPostalCode()) {
                    $city = $this->franceCities->getCityByPostalCodeAndName($address->getPostalCode(), $address->getCityName());

                    if ($city) {
                        $address->setCity(sprintf('%s-%s', $address->getPostalCode(), $city->getInseeCode()));
                    }
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Address::class,
            ]);
    }
}
