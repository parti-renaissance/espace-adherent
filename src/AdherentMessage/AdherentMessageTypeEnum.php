<?php

namespace AppBundle\AdherentMessage;

use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;
use MyCLabs\Enum\Enum;

class AdherentMessageTypeEnum extends Enum
{
    public const DEPUTY = 'deputy';
    public const REFERENT = 'referent';
    public const HOST = 'host';
    public const SUPERVISOR = 'supervisor';

    private static $classMapping = [
        self::DEPUTY => DeputyAdherentMessage::class,
        self::REFERENT => ReferentAdherentMessage::class,
    ];

    public static function getClassName(string $type): ?string
    {
        return self::$classMapping[$type] ?? null;
    }

    public static function getType(string $className): ?string
    {
        return array_flip(self::$classMapping)[$className] ?? null;
    }
}
