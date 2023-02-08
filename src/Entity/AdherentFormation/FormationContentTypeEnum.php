<?php

namespace App\Entity\AdherentFormation;

enum FormationContentTypeEnum: string
{
    public const FILE = 'file';
    public const LINK = 'link';

    public const ALL = [
        self::FILE,
        self::LINK,
    ];
}
