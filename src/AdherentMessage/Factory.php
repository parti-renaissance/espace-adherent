<?php

namespace AppBundle\AdherentMessage;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;
use Ramsey\Uuid\Uuid;

class Factory
{
    public static function create(Adherent $adherent, AdherentMessageDataObject $dataObject): AdherentMessageInterface
    {
        switch ($dataObject->getType()) {
            case AdherentMessageTypeEnum::DEPUTY:
                $message = new DeputyAdherentMessage(Uuid::uuid4(), $adherent);
                break;

            case AdherentMessageTypeEnum::REFERENT:
                $message = new ReferentAdherentMessage(Uuid::uuid4(), $adherent);
                break;
        }

        if (!isset($message)) {
            throw new \InvalidArgumentException(sprintf('Message type "%s" is undefined', $dataObject->getType()));
        }

        return $message->updateFromDataObject($dataObject);
    }
}
