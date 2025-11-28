<?php

declare(strict_types=1);

namespace App\Form;

use App\Address\AddressInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType as SfCountryType;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReCountryType extends AbstractType
{
    private const EXCLUDED_TERRITORIES = [
        'GP', 'MQ', 'GF', 'RE', 'PM', 'YT', 'BL', 'MF', 'WF', 'PF', 'NC', 'TF', // Territoires français
        'AI', 'BM', 'IO', 'FK', 'GI', 'MS', 'PN', 'SH', 'TC', 'VG', // Territoires britanniques
        'AS', 'GU', 'MP', 'PR', 'UM', 'VI', // Territoires américains
        'AW', 'CW', 'SX', 'BQ', // Territoires néerlandais
        'CC', 'CX', 'HM', 'NF', // Territoires australiens
        'CK', 'NU', 'TK', // Autres territoires
    ];

    public function configureOptions(OptionsResolver $resolver): void
    {
        $countries = Countries::getNames();
        $filteredCountries = array_filter($countries, function ($code) {
            return !\in_array($code, self::EXCLUDED_TERRITORIES, true);
        }, \ARRAY_FILTER_USE_KEY);

        $resolver->setDefaults([
            'choices' => array_flip($filteredCountries),
            'label' => 'Pays',
            'preferred_choices' => [AddressInterface::FRANCE],
            'choice_loader' => null,
        ]);
    }

    public function getParent(): string
    {
        return SfCountryType::class;
    }
}
