<?php

namespace AppBundle\AdherentMessage;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;
use Ramsey\Uuid\Uuid;

class AdherentMessageFactory
{
    public static function create(
        Adherent $adherent,
        AdherentMessageDataObject $dataObject,
        string $type
    ): AdherentMessageInterface {
        switch ($type) {
            case AdherentMessageTypeEnum::DEPUTY:
                $message = new DeputyAdherentMessage(Uuid::uuid4(), $adherent);
                break;

            case AdherentMessageTypeEnum::REFERENT:
                $message = new ReferentAdherentMessage(Uuid::uuid4(), $adherent);
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Message type "%s" is undefined', $type));
        }

        return $message->updateFromDataObject($dataObject);
    }
}
