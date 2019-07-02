<?php

namespace AppBundle\Oldolf;

use AppBundle\Oldolf\Measure\BaisseNombreChomeurs;
use AppBundle\Oldolf\Measure\ChequeEnergie;
use AppBundle\Oldolf\Measure\ConversionSurfaceAgricoleBio;
use AppBundle\Oldolf\Measure\CouvertureFibre;
use AppBundle\Oldolf\Measure\CreationEntreprise;
use AppBundle\Oldolf\Measure\CreationPoliceSecuriteQuotidien;
use AppBundle\Oldolf\Measure\EmploisFrancs;
use AppBundle\Oldolf\Measure\MaisonServiceAccueilPublic;
use AppBundle\Oldolf\Measure\PassCulture;
use AppBundle\Oldolf\Measure\PrimeConversionAutomobile;
use AppBundle\Oldolf\Measure\SuppressionTaxeHabitation;

class MeasureChoiceLoader
{
    private const TYPES = [
        BaisseNombreChomeurs::class,
        ChequeEnergie::class,
        ConversionSurfaceAgricoleBio::class,
        CouvertureFibre::class,
        CreationEntreprise::class,
        CreationPoliceSecuriteQuotidien::class,
        EmploisFrancs::class,
        MaisonServiceAccueilPublic::class,
        PassCulture::class,
        PrimeConversionAutomobile::class,
        SuppressionTaxeHabitation::class,
    ];

    public static function getTypeKeysMap(): array
    {
        $map = [];
        foreach (self::TYPES as $typeClass) {
            $map[$typeClass::getType()] = $typeClass::getKeys();
        }

        return $map;
    }

    public static function getTypeChoices(): array
    {
        $choices = [];
        foreach (array_keys(self::getTypeKeysMap()) as $type) {
            $choices["oldolf.measure.type.$type"] = $type;
        }

        return $choices;
    }

    public static function getKeyChoices(): array
    {
        $choices = [];
        foreach (self::getTypeKeysMap() as $keys) {
            foreach (array_keys($keys) as $key) {
                $choices[$key] = $key;
            }
        }

        return $choices;
    }
}
