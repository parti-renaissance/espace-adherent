<?php

namespace App\Entity\ElectedRepresentative;

use MyCLabs\Enum\Enum;

final class CandidateNameEnum extends Enum
{
    public const FRANCOIS_BAYROU = 'François Bayrou';
    public const OLIVIER_BESANCENOT = 'Olivier Besancenot';
    public const JOSE_BOVE = 'José Bové';
    public const MARIE_GEORGE_BUFFET = 'Marie-George Buffet';
    public const ARLETTE_LAGUILLER = 'Arlette Laguiller';
    public const JEAN_MARIE_LE_PEN = 'Jean-Marie Le Pen';
    public const FREDERIC_NIHOUS = 'Frédéric Nihous';
    public const SEGOLENE_ROYAL = 'Ségolène Royal';
    public const NICOLAS_SARKOZY = 'Nicolas Sarkozy';
    public const GERARD_SCHIVARDI = 'Gérard Schivardi';
    public const PHILIPPE_DE_VILLIERS = 'Philippe de Villiers';
    public const DOMINIQUE_VOYNET = 'Dominique Voynet';

    public const NATHALIE_ARTHAUD = 'Nathalie Arthaud';
    public const JACQUES_CHEMINADE = 'Jacques Cheminade';
    public const NICOLAS_DUPONT_AIGNAN = 'Nicolas Dupont-Aignan';
    public const FRANCOIS_HOLLANDE = 'François Hollande';
    public const EVA_JOLY = 'Eva Joly';
    public const MARINE_LE_PEN = 'Marine Le Pen';
    public const JEAN_LUC_MELENCHON = 'Jean-Luc Mélenchon';
    public const PHILIPPE_POUTOU = 'Philippe Poutou';

    public const FRANCOIS_ASSELINEAU = 'François Asselineau';
    public const BENOIT_HAMON = 'Benoît Hamon';
    public const FRANCOIS_FILLON = 'François Fillon';
    public const JEAN_LASSALLE = 'Jean Lassalle';
    public const EMMANUEL_MACRON = 'Emmanuel Macron';

    public const OTHER = 'Autre candidat';

    public const ALL = [
        2007 => [
            self::FRANCOIS_BAYROU,
            self::OLIVIER_BESANCENOT,
            self::JOSE_BOVE,
            self::MARIE_GEORGE_BUFFET,
            self::ARLETTE_LAGUILLER,
            self::JEAN_MARIE_LE_PEN,
            self::FREDERIC_NIHOUS,
            self::SEGOLENE_ROYAL,
            self::NICOLAS_SARKOZY,
            self::GERARD_SCHIVARDI,
            self::PHILIPPE_DE_VILLIERS,
            self::DOMINIQUE_VOYNET,
            self::OTHER,
        ],
        2012 => [
            self::NATHALIE_ARTHAUD,
            self::FRANCOIS_BAYROU,
            self::JACQUES_CHEMINADE,
            self::NICOLAS_DUPONT_AIGNAN,
            self::FRANCOIS_HOLLANDE,
            self::EVA_JOLY,
            self::MARINE_LE_PEN,
            self::JEAN_LUC_MELENCHON,
            self::PHILIPPE_POUTOU,
            self::NICOLAS_SARKOZY,
            self::OTHER,
        ],
        2017 => [
            self::NATHALIE_ARTHAUD,
            self::FRANCOIS_ASSELINEAU,
            self::JACQUES_CHEMINADE,
            self::NICOLAS_DUPONT_AIGNAN,
            self::BENOIT_HAMON,
            self::FRANCOIS_FILLON,
            self::JEAN_LASSALLE,
            self::MARINE_LE_PEN,
            self::EMMANUEL_MACRON,
            self::JEAN_LUC_MELENCHON,
            self::PHILIPPE_POUTOU,
            self::OTHER,
        ],
        2022 => [
            self::OTHER,
        ],
    ];
}
