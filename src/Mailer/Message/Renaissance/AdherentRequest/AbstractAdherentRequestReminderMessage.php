<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\AdherentRequest;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Symfony\Component\Uid\Uuid;

abstract class AbstractAdherentRequestReminderMessage extends AbstractRenaissanceMessage
{
    public static function create(AdherentRequest $adherentRequest, string $adhesionLink): self
    {
        return new static(
            Uuid::v4(),
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
