<?php

namespace AppBundle\Utils;

use AppBundle\Intl\FranceCitiesBundle;

class AreaUtils
{
    public const CODE_CORSICA_A = '2A';
    public const CODE_CORSICA_B = '2B';
    public const CODE_FRANCE = 'FR';
    public const CODE_MONACO = '06';
    public const CODE_SAINT_BARTHELEMY = '97133';
    public const CODE_SAINT_MARTIN = '97150';
    public const POSTALCODE_MONACO = '98000';
    public const PREFIX_POSTALCODE_CORSICA = '20';
    public const PREFIX_POSTALCODE_CORSICA_A = ['200', '201'];
    public const PREFIX_POSTALCODE_DOM = '97';
    public const PREFIX_POSTALCODE_PARIS_DISTRICTS = '75';
    public const PREFIX_POSTALCODE_TOM = '98';

    public static function getCodeFromPostalCode(?string $postalCode): ?string
    {
        $department = mb_substr($postalCode, 0, 2);

        switch ($department) {
            case self::PREFIX_POSTALCODE_PARIS_DISTRICTS:
                return $postalCode;
            case self::PREFIX_POSTALCODE_TOM:
                return self::POSTALCODE_MONACO === $postalCode ? self::CODE_MONACO : mb_substr($postalCode, 0, 3);
            case self::PREFIX_POSTALCODE_DOM:
                if (\in_array($postalCode, [self::CODE_SAINT_BARTHELEMY, self::CODE_SAINT_MARTIN], true)) {
                    return $postalCode;
                }

                return mb_substr($postalCode, 0, 3);
            case self::PREFIX_POSTALCODE_CORSICA:
                return \in_array(mb_substr($postalCode, 0, 3), self::PREFIX_POSTALCODE_CORSICA_A, true)
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

        $code = (string) array_search($country, FranceCitiesBundle::$countries);

        return self::POSTALCODE_MONACO === $code ? self::CODE_MONACO : $code;
    }

    public static function getRelatedCodes(string $code): array
    {
        $relatedCodes = [];

        if (static::isParisCode($code)) {
            $relatedCodes[] = self::PREFIX_POSTALCODE_PARIS_DISTRICTS;
        }

        if (static::isCorsicaCode($code)) {
            $relatedCodes[] = self::PREFIX_POSTALCODE_CORSICA;
        }

        return $relatedCodes;
    }

    private static function isParisCode(string $code): bool
    {
        return self::PREFIX_POSTALCODE_PARIS_DISTRICTS === mb_substr($code, 0, 2);
    }

    private static function isCorsicaCode(string $code): bool
    {
        return \in_array($code, [self::CODE_CORSICA_A, self::CODE_CORSICA_B], true);
    }
}
