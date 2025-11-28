<?php

declare(strict_types=1);

namespace App\Form;

use App\Address\Address;
use App\Address\AddressInterface;
use App\Form\DataTransformer\CityNameDataTransformer;
use App\FranceCities\FranceCities;
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
    private FranceCities $franceCities;
    private CityNameDataTransformer $cityNameDataTransformer;

    public function __construct(FranceCities $franceCities, CityNameDataTransformer $cityNameDataTransformer)
    {
        $this->franceCities = $franceCities;
        $this->cityNameDataTransformer = $cityNameDataTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => $options['strict_mode'],
            ])
        ;

        if ($options['with_additional_address']) {
            $builder->add('additionalAddress', TextType::class, ['required' => false]);
        }

        if ($options['with_city']) {
            $builder
                ->add('city', HiddenType::class, [
                    'required' => false,
                    'error_bubbling' => $options['child_error_bubbling'],
                    'disabled' => $options['disable_fields'],
                ])
            ;
        }

        $builder
            ->add('cityName', TextType::class, [
                'label' => 'Ville',
                'required' => false,
                'disabled' => $options['disable_fields'],
            ])
            ->add('country', ReCountryType::class, [
                'disabled' => $options['disable_fields'],
                'placeholder' => 'Sélectionner un pays',
                'invalid_message' => 'common.country.invalid',
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'error_bubbling' => $options['child_error_bubbling'],
                'disabled' => $options['disable_fields'],
            ])
        ;

        if ($options['set_address_region']) {
            $builder->add('region', TextType::class, [
                'label' => 'Région',
                'required' => false,
                'disabled' => $options['disable_fields'],
            ]);
        }

        $builder
            ->addModelTransformer($this->cityNameDataTransformer)
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Address $address */
                $address = $event->getData();

                if ($address && $address->getCityName() && $address->getPostalCode() && AddressInterface::FRANCE === $address->getCountry()) {
                    $city = $this->franceCities->getCityByPostalCodeAndName($address->getPostalCode(), $address->getCityName());

                    if ($city) {
                        $address->setCity(\sprintf('%s-%s', $address->getPostalCode(), $city->getInseeCode()));
                    }
                }
            })
            ->get('postalCode')->addModelTransformer(new CallbackTransformer(
                function ($data) { return $data; },
                function ($value) { return str_replace(' ', '', $value); }
            ))
        ;
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
                'strict_mode' => true,
                'with_city' => true,
                'with_additional_address' => false,
            ])
            ->setAllowedTypes('with_additional_address', 'bool')
            ->setAllowedTypes('disable_fields', 'bool')
            ->setAllowedTypes('child_error_bubbling', 'bool')
            ->setAllowedTypes('strict_mode', 'bool')
            ->setAllowedTypes('with_city', 'bool')
        ;
    }
}
