<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdhesionReportMessage extends Message
{
    public static function create(Adherent $recipient, int $newAdherents, int $newSubscribedAdherents): self
    {
        return new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            'Adhésions : bilan hebdomadaire',
            [
                'new_adherents' => $newAdherents,
                'new_subscribed_adherents' => $newSubscribedAdherents,
            ]
        );
    }
}
