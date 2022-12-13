<?php

namespace App\Entity\AdherentFormation;

enum FormationContentTypeEnum: string
{
    case FILE = 'file';
    case LINK = 'link';

    public const ALL = [
        self::FILE,
        self::LINK,
    ];
}
