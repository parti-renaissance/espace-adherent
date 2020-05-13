<?php

namespace App\ChezVous;

use App\ChezVous\Measure\BaisseNombreChomeurs;
use App\ChezVous\Measure\ChequeEnergie;
use App\ChezVous\Measure\ConversionSurfaceAgricoleBio;
use App\ChezVous\Measure\CouvertureFibre;
use App\ChezVous\Measure\CreationEntreprise;
use App\ChezVous\Measure\DedoublementClasses;
use App\ChezVous\Measure\EmploisFrancs;
use App\ChezVous\Measure\MaisonServiceAccueilPublic;
use App\ChezVous\Measure\MissionBern;
use App\ChezVous\Measure\PassCulture;
use App\ChezVous\Measure\PrimeConversionAutomobile;
use App\ChezVous\Measure\QuartierReconqueteRepublicaine;
use App\ChezVous\Measure\SuppressionTaxeHabitation;
use App\Entity\ChezVous\MeasureType;
use App\Repository\ChezVous\MeasureTypeRepository;

class MeasureChoiceLoader
{
    private $measureTypeRepository;
    private $cachedMeasureTypes = [];

    public function __construct(MeasureTypeRepository $measureTypeRepository)
    {
        $this->measureTypeRepository = $measureTypeRepository;
    }

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
        MissionBern::class,
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

    public function getMeasureType(string $code): ?MeasureType
    {
        if (!\array_key_exists($code, $this->cachedMeasureTypes)) {
            if (!$measureType = $this->measureTypeRepository->findOneByCode($code)) {
                throw new \InvalidArgumentException(sprintf('There is no MeasureType with code "%s" found in database.', $type));
            }

            $this->cachedMeasureTypes[$code] = $measureType;
        }

        return $this->cachedMeasureTypes[$code];
    }
}
