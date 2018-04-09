<?php

namespace AppBundle\Utils;

use AppBundle\Intl\FranceCitiesBundle;

class AreaUtils
{
    const CODE_CORSICA_A = '2A';
    const CODE_CORSICA_B = '2B';
    const CODE_FRANCE = 'FR';
    const CODE_MONACO = '06';
    const POSTALCODE_MONACO = '98000';
    const PREFIX_POSTALCODE_CORSICA = '20';
    const PREFIX_POSTALCODE_CORSICA_A = ['200', '201'];
    const PREFIX_POSTALCODE_DOM = '97';
    const PREFIX_POSTALCODE_PARIS_DISTRICTS = '75';
    const PREFIX_POSTALCODE_TOM = '98';

    public static function getCodeFromPostalCode(?string $postalCode): ?string
    {
        $department = \substr($postalCode, 0, 2);

        switch ($department) {
            case self::PREFIX_POSTALCODE_PARIS_DISTRICTS:
                return $postalCode;
            case self::PREFIX_POSTALCODE_TOM:
                return self::POSTALCODE_MONACO === $postalCode ? self::CODE_MONACO : \substr($postalCode, 0, 3);
            case self::PREFIX_POSTALCODE_DOM:
                return \substr($postalCode, 0, 3);
            case self::PREFIX_POSTALCODE_CORSICA:
                return \in_array(substr($postalCode, 0, 3), self::PREFIX_POSTALCODE_CORSICA_A, true)
                    ? self::CODE_CORSICA_A
                    : self::CODE_CORSICA_B
                ;
            default:
                return $department;
        }
    }

    public static function getCodeFromCountry(string $country): string
    {
        if (!\in_array($country, FranceCitiesBundle::$countries, true)) {
            return $country;
        }

        $code = (string) \array_search($country, FranceCitiesBundle::$countries);

        return self::POSTALCODE_MONACO === $code ? self::CODE_MONACO : $code;
    }
}
