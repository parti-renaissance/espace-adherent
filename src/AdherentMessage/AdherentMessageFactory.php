<?php

namespace AppBundle\AdherentMessage;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use AppBundle\Entity\AdherentMessage\CommitteeAdherentMessage;
use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
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

            case AdherentMessageTypeEnum::COMMITTEE:
                $message = new CommitteeAdherentMessage(Uuid::uuid4(), $adherent);
                break;

            case AdherentMessageTypeEnum::CITIZEN_PROJECT:
                $message = new CitizenProjectAdherentMessage(Uuid::uuid4(), $adherent);
                break;

            case AdherentMessageTypeEnum::MUNICIPAL_CHIEF:
                $message = new MunicipalChiefAdherentMessage(Uuid::uuid4(), $adherent);
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Message type "%s" is undefined', $type));
        }

        return $message->updateFromDataObject($dataObject);
    }
}
