<?php

namespace AppBundle\AdherentMessage;

final class Utils
{
    public static function getMessageTypeFromUri(string $uri): ?string
    {
        if (false !== strpos($uri, 'espace-referent/')) {
            return AdherentMessageTypeEnum::REFERENT;
        }

        if (false !== strpos($uri, 'espace-depute/')) {
            return AdherentMessageTypeEnum::DEPUTY;
        }

        return null;
    }
}
