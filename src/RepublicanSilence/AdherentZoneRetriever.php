<?php

namespace AppBundle\RepublicanSilence;

use AppBundle\Entity\Adherent;

class AdherentZoneRetriever
{
    public const ADHERENT_TYPE_REFERENT = 0;
    public const ADHERENT_TYPE_ANIMATOR = 1;
    public const ADHERENT_TYPE_HOST = 2;

    public static function getAdherentZone(Adherent $user, int $type): array
    {
        switch ($type) {
            case self::ADHERENT_TYPE_REFERENT:
                return $user->getManagedArea()->getCodes();
            case self::ADHERENT_TYPE_ANIMATOR:
                return [];
            case self::ADHERENT_TYPE_HOST:
                return [];
            default:
                return [];
        }
    }
}
