<?php

namespace App\Entity\ElectedRepresentative;

use MyCLabs\Enum\Enum;

final class LabelNameEnum extends Enum
{
    public const LFI = 'LFI';
    public const PCF = 'PCF';
    public const PS = 'PS';
    public const MRC = 'MRC';
    public const GS = 'G.s';
    public const TDP = 'TDP';
    public const LAREM = 'LaREM';
    public const MODEM = 'Modem';
    public const MRSL = 'MRSL';
    public const AGIR = 'Agir';
    public const UDI = 'UDI';
    public const LR = 'LR';
    public const DLF = 'DLF';
    public const RN = 'RN';
    public const OTHER = 'Autre parti';

    public const ALL = [
        self::LFI,
        self::PCF,
        self::PS,
        self::MRC,
        self::GS,
        self::TDP,
        self::LAREM,
        self::MODEM,
        self::MRSL,
        self::AGIR,
        self::UDI,
        self::LR,
        self::DLF,
        self::RN,
        self::OTHER,
    ];
}
