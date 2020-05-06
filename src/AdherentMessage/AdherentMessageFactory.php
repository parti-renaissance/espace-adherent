<?php

namespace App\AdherentMessage;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use App\Entity\AdherentMessage\ReferentAdherentMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
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

            case AdherentMessageTypeEnum::SENATOR:
                $message = new SenatorAdherentMessage(Uuid::uuid4(), $adherent);
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Message type "%s" is undefined', $type));
        }

        return $message->updateFromDataObject($dataObject);
    }
}
