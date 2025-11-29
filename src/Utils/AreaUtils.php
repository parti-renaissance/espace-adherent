<?php

declare(strict_types=1);

namespace App\Utils;

use App\Address\AddressInterface;
use App\Entity\EntityPostAddressInterface;
use App\Intl\FranceCitiesBundle;

class AreaUtils
{
    public const CODE_CORSICA_A = '2A';
    public const CODE_CORSICA_B = '2B';
    public const CODE_RHONE = '69';
    public const CODE_NOUVEAU_RHONE = '69D';
    public const CODE_METROPOLIS_MONTPELLIER = '34M';
    public const CODE_METROPOLIS_LYON = '69M';
    public const CODE_MONACO = 'MC';
    public const CODE_SAINT_BARTHELEMY = '97133';
    public const CODE_SAINT_MARTIN = '97150';
    public const POSTALCODE_MONACO = '98000';
    public const PREFIX_POSTALCODE_CORSICA = '20';
    public const PREFIX_POSTALCODE_CORSICA_A = ['200', '201'];
    public const PREFIX_POSTALCODE_DOM = '97';
    public const PREFIX_POSTALCODE_PARIS_DISTRICTS = '75';
    public const PREFIX_POSTALCODE_TOM = '98';
    public const METROPOLIS = [
        self::CODE_METROPOLIS_MONTPELLIER => [
            '34022',
            '34027',
            '34057',
            '34058',
            '34077',
            '34087',
            '34088',
            '34090',
            '34095',
            '34116',
            '34120',
            '34123',
            '34129',
            '34134',
            '34164',
            '34169',
            '34172',
            '34179',
            '34198',
            '34202',
            '34217',
            '34227',
            '34244',
            '34249',
            '34256',
            '34259',
            '34270',
            '34295',
            '34307',
            '34327',
            '34337',
        ],
        self::CODE_METROPOLIS_LYON => [
            '69003',
            '69029',
            '69033',
            '69034',
            '69040',
            '69044',
            '69046',
            '69271',
            '69063',
            '69273',
            '69068',
            '69069',
            '69071',
            '69072',
            '69275',
            '69081',
            '69276',
            '69085',
            '69087',
            '69088',
            '69089',
            '69278',
            '69091',
            '69096',
            '69100',
            '69279',
            '69116',
            '69117',
            '69123',
            '69127',
            '69282',
            '69283',
            '69284',
            '69142',
            '69143',
            '69149',
            '69152',
            '69153',
            '69163',
            '69286',
            '69168',
            '69191',
            '69194',
            '69199',
            '69204',
            '69205',
            '69207',
            '69290',
            '69233',
            '69202',
            '69292',
            '69293',
            '69296',
            '69244',
            '69250',
            '69256',
            '69259',
            '69260',
            '69266',
            // Lyon district INSEE codes
            '69381',
            '69382',
            '69383',
            '69384',
            '69385',
            '69386',
            '69387',
            '69388',
            '69389',
        ],
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
                    : self::CODE_CORSICA_B;
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

    public static function getMetropolisCode(EntityPostAddressInterface $entity): ?string
    {
        if (AddressInterface::FRANCE === $entity->getCountry()) {
            foreach (self::METROPOLIS as $codeM => $codes) {
                if (\in_array($entity->getInseeCode(), $codes, true)) {
                    return $codeM;
                }
            }
        }

        return null;
    }

    public static function get69DCode(EntityPostAddressInterface $entity): ?string
    {
        return (AddressInterface::FRANCE === $entity->getCountry()
                && self::CODE_RHONE === substr($entity->getPostalCode(), 0, 2)
                && !\in_array($entity->getInseeCode(), self::METROPOLIS[self::CODE_METROPOLIS_LYON]))
            ? self::CODE_NOUVEAU_RHONE
            : null;
    }

    public static function getRelatedCodes(string $code): array
    {
        $relatedCodes = [];

        if (self::isParisCode($code)) {
            $relatedCodes[] = self::PREFIX_POSTALCODE_PARIS_DISTRICTS;
        }

        if (self::isCorsicaCode($code)) {
            $relatedCodes[] = self::PREFIX_POSTALCODE_CORSICA;
        }

        return $relatedCodes;
    }

    public static function getZone(EntityPostAddressInterface $entity): ?string
    {
        if (AddressInterface::FRANCE === $entity->getCountry()) {
            // for cities in the department starting by 0, the code contains only 4 figures
            // but a zone in this case should start with 0
            $inseeCode = $entity->getInseeCode();

            return $inseeCode ? str_pad($inseeCode, 5, '0', \STR_PAD_LEFT) : null;
        }

        return $entity->getCountry();
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
