<?php

declare(strict_types=1);

namespace App\QrCode;

class QrCodeHostEnum
{
    public const HOST_ENMARCHE = 'enmarche';
    public const HOST_RENAISSANCE = 'renaissance';

    public const ALL = [
        self::HOST_ENMARCHE,
        self::HOST_RENAISSANCE,
    ];
}
