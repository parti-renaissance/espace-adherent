<?php

namespace AppBundle\Election;

use MyCLabs\Enum\Enum;

final class VoteListNuanceEnum extends Enum
{
    public const REM = 'REM';
    public const EXG = 'EXG';
    public const COM = 'COM';
    public const FI = 'FI';
    public const SOC = 'SOC';
    public const GEN = 'GEN';
    public const RDG = 'RDG';
    public const DVG = 'DVG';
    public const VEC = 'VEC';
    public const ECO = 'ECO';
    public const DIV = 'DIV';
    public const ANM = 'ANM';
    public const REG = 'REG';
    public const GJ = 'GJ';
    public const MDM = 'MDM';
    public const UDI = 'UDI';
    public const AGR = 'AGR';
    public const MR = 'MR';
    public const DVC = 'DVC';
    public const LR = 'LR';
    public const DVD = 'DVD';
    public const DLF = 'DLF';
    public const RN = 'RN';
    public const EXD = 'EXD';
    public const UG = 'UG';
    public const UC = 'UC';
    public const UD = 'UD';

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
            'Union de la gauche' => self::UG,
            'Union du centre' => self::UC,
            'Union de la droite' => self::UD,
        ];
    }
}
