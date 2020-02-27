<?php

namespace AppBundle\Election;

use MyCLabs\Enum\Enum;

final class VoteListNuanceEnum extends Enum
{
    private const EXG = 'EXG';
    private const COM = 'COM';
    private const FI = 'FI';
    private const SOC = 'SOC';
    private const GEN = 'GEN';
    private const RDG = 'RDG';
    private const DVG = 'DVG';
    private const VEC = 'VEC';
    private const ECO = 'ECO';
    private const DIV = 'DIV';
    private const ANM = 'ANM';
    private const REG = 'REG';
    private const GJ = 'GJ';
    private const REM = 'REM';
    private const MDM = 'MDM';
    private const UDI = 'UDI';
    private const AGR = 'AGR';
    private const MR = 'MR';
    private const DVC = 'DVC';
    private const LR = 'LR';
    private const DVD = 'DVD';
    private const DLF = 'DLF';
    private const RN = 'RN';
    private const EXD = 'EXD';

    public static function getChoices(): array
    {
        return [
            'Extrême gauche' => self::EXG,
            'Parti communiste français' => self::COM,
            'La France insoumise' => self::FI,
            'Parti socialiste' => self::SOC,
            'Génération.s' => self::GEN,
            'Parti radical de gauche' => self::RDG,
            'Divers gauche ' => self::DVG,
            'Europe Ecologie-Les Verts' => self::VEC,
            'Ecologiste' => self::ECO,
            'Divers' => self::DIV,
            'Animaliste' => self::ANM,
            'Régionaliste' => self::REG,
            'Gilets jaunes' => self::GJ,
            'La République en Marche' => self::REM,
            'Modem' => self::MDM,
            'Union des Démocrates et Indépendants' => self::UDI,
            'Agir' => self::AGR,
            'Mouvement radical / social-libéral' => self::MR,
            'Divers centre' => self::DVC,
            'Les Républicains' => self::LR,
            'Divers droite' => self::DVD,
            'Debout la France' => self::DLF,
            'Rassemblement National' => self::RN,
            'Extrême droite' => self::EXD,
        ];
    }
}
