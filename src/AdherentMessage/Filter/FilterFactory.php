<?php

namespace AppBundle\AdherentMessage\Filter;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;

class FilterFactory
{
    public static function create(string $messageType): FilterDataObjectInterface
    {
        switch ($messageType) {
            case AdherentMessageTypeEnum::REFERENT:
                return new ReferentFilterDataObject();
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Message type "%s" is unsupported by filter page', $messageType));
        }
    }
}
