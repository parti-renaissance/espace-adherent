<?php

namespace App\AdherentMessage;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Ramsey\Uuid\Uuid;

class AdherentMessageFactory
{
    public static function create(
        Adherent $adherent,
        AdherentMessageDataObject $dataObject,
        string $type,
    ): AdherentMessageInterface {
        $className = static::getMessageClassName($type);

        return (new $className(Uuid::uuid4(), $adherent))->updateFromDataObject($dataObject);
    }

    public static function getMessageClassName(string $scopeCode, ?string $type = null): string
    {
        if (!$className = AdherentMessageTypeEnum::getMessageClassFromScopeCode($scopeCode, $type)) {
            throw new \InvalidArgumentException(\sprintf('Message type "%s" is undefined', $type));
        }

        return $className;
    }
}
