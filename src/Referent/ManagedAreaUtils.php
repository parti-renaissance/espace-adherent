<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Utils\AreaUtils;

class ManagedAreaUtils extends AreaUtils
{
    public static function getCodeFromCommittee(Committee $committee): ?string
    {
        if (self::CODE_FRANCE === $committee->getCountry()) {
            return static::getCodeFromPostalCode($committee->getPostalCode());
        }

        return static::getCodeFromCountry($committee->getCountry());
    }

    public static function getCodeFromEvent(Event $event): ?string
    {
        if (self::CODE_FRANCE === $event->getCountry()) {
            return static::getCodeFromPostalCode($event->getPostalCode());
        }

        return static::getCodeFromCountry($event->getCountry());
    }

    public static function getCodeFromAdherent(Adherent $adherent): string
    {
        if (self::CODE_FRANCE === $adherent->getCountry()) {
            return static::getCodeFromPostalCode($adherent->getPostalCode());
        }

        return static::getCodeFromCountry($adherent->getCountry());
    }
}
