<?php

namespace App\QrCode;

class QrCodeHostEnum
{
    public const HOST_ENMARCHE = 'enmarche';
    public const HOST_AVECVOUS = 'avecvous';
    public const HOST_RENAISSANCE = 'renaissance';

    public const ALL = [
        self::HOST_ENMARCHE,
        self::HOST_AVECVOUS,
        self::HOST_RENAISSANCE,
    ];
}
