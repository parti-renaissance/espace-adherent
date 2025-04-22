<?php

namespace App\Mailer\Message\Renaissance\AdherentRequest;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Ramsey\Uuid\Uuid;

abstract class AbstractAdherentRequestReminderMessage extends AbstractRenaissanceMessage
{
    public static function create(AdherentRequest $adherentRequest, string $adhesionLink): self
    {
        return new static(
            Uuid::uuid4(),
            $adherentRequest->email,
            $adherentRequest->email,
            static::getReminderSubject(),
            [
                'primary_link' => $adhesionLink,
            ],
        );
    }

    abstract protected static function getReminderSubject(): string;
}
