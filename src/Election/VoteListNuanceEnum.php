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
    public const NC = 'NC';
    public const UMP = 'UMP';
    public const FN = 'FN';
    public const PG = 'PG';
    public const UDF = 'UDF';
    public const PRG = 'PRG';
    public const ALLI = 'ALLI';
    public const LO = 'LO';
    public const CPNT = 'CPNT';
    public const FG = 'FG';
    public const LCR = 'LCR';
    public const PRV = 'PRV';
    public const MDC = 'MDC';
    public const MNR = 'MNR';

    public static function getChoices(): array
    {
        return [
            'Extrême gauche' => self::EXG,
            'Parti communiste français' => self::COM,
            'La France insoumise' => self::FI,
            'Parti socialiste' => self::SOC,
            'Génération.s' => self::GEN,
            'Parti radical de gauche' => self::PRG,
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
            'Nouveau Centre' => self::NC,
            'Union pour un Mouvement populaire' => self::UMP,
            'Front national' => self::FN,
            'Parti de gauche' => self::PG,
            'Union pour la démocratie française' => self::UDF,
            'Alliance centriste' => self::ALLI,
            'Lutte ouvrière' => self::LO,
            'Chasse, pêche, nature et traditions' => self::CPNT,
            'Front de gauche' => self::FG,
            'Ligue communiste révolutionnaire' => self::LCR,
            'Parti radical valoisien' => self::PRV,
            'Mouvement des citoyens' => self::MDC,
            'Mouvement national républicain' => self::MNR,
        ];
    }
}
