<?php

namespace AppBundle\ChezVous;

use AppBundle\ChezVous\Measure\BaisseNombreChomeurs;
use AppBundle\ChezVous\Measure\ChequeEnergie;
use AppBundle\ChezVous\Measure\ConversionSurfaceAgricoleBio;
use AppBundle\ChezVous\Measure\CouvertureFibre;
use AppBundle\ChezVous\Measure\CreationEntreprise;
use AppBundle\ChezVous\Measure\DedoublementClasses;
use AppBundle\ChezVous\Measure\EmploisFrancs;
use AppBundle\ChezVous\Measure\MaisonServiceAccueilPublic;
use AppBundle\ChezVous\Measure\PassCulture;
use AppBundle\ChezVous\Measure\PrimeConversionAutomobile;
use AppBundle\ChezVous\Measure\QuartierReconqueteRepublicaine;
use AppBundle\ChezVous\Measure\SuppressionTaxeHabitation;

class MeasureChoiceLoader
{
    private const TYPES = [
        DedoublementClasses::class,
        MaisonServiceAccueilPublic::class,
        SuppressionTaxeHabitation::class,
        PassCulture::class,
        CreationEntreprise::class,
        BaisseNombreChomeurs::class,
        EmploisFrancs::class,
        CouvertureFibre::class,
        PrimeConversionAutomobile::class,
        ChequeEnergie::class,
        ConversionSurfaceAgricoleBio::class,
        QuartierReconqueteRepublicaine::class,
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
            $choices["chez_vous.measure.type.$type"] = $type;
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
