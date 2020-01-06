<?php

namespace AppBundle\Utils;

use AppBundle\Entity\District;
use AppBundle\Intl\FranceCitiesBundle;

class AreaUtils
{
    public const CODE_CORSICA_A = '2A';
    public const CODE_CORSICA_B = '2B';
    public const CODE_FRANCE = 'FR';
    public const CODE_MONACO = 'MC';
    public const CODE_SAINT_BARTHELEMY = '97133';
    public const CODE_SAINT_MARTIN = '97150';
    public const POSTALCODE_MONACO = '98000';
    public const PREFIX_POSTALCODE_CORSICA = '20';
    public const PREFIX_POSTALCODE_CORSICA_A = ['200', '201'];
    public const PREFIX_POSTALCODE_DOM = '97';
    public const PREFIX_POSTALCODE_PARIS_DISTRICTS = '75';
    public const PREFIX_POSTALCODE_TOM = '98';
    public const DISTRICT_PARIS = [
        '75001' => ['75001', '75002', '75008', '75009'],
        '75002' => ['75005', '75006', '75007'],
        '75003' => ['75017', '75018'],
        '75004' => ['75016', '75017'],
        '75005' => ['75003', '75010'],
        '75006' => ['75011', '75020'],
        '75007' => ['75004', '75011', '75012'],
        '75008' => ['75012', '75020'],
        '75009' => ['75013'],
        '75010' => ['75013', '75014'],
        '75011' => ['75006', '75014'],
        '75012' => ['75007', '75015'],
        '75013' => ['75015'],
        '75014' => ['75016'],
        '75015' => ['75020'],
        '75016' => ['75019'],
        '75017' => ['75018', '75019'],
        '75018' => ['75009', '75018'],
    ];

    public const INSEE_CODE_ANNECY = '74010';
    public const INSEE_CODES_ATTACHED_TO_ANNECY = [
        '74011',
        '74268',
        '74093',
        '74182',
        '74217',
    ];

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

    public static function getCodeFromDistrict(District $district): array
    {
        if ($district->isFrenchDistrict()) {
            $code = $district->getDepartmentCode();
            if (self::PREFIX_POSTALCODE_PARIS_DISTRICTS === $code) {
                return array_merge(['FR', '75'], self::DISTRICT_PARIS[$district->getCode()]);
            } else {
                return ['FR', $district->getDepartmentCode()];
            }
        } else {
            foreach ($district->getCountries() as $country) {
                $codes[] = self::getCodeFromCountry($country);
            }

            return $codes;
        }
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
