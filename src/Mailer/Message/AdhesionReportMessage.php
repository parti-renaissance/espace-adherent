<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\Adherent;
use Symfony\Component\Uid\Uuid;

final class AdhesionReportMessage extends Message
{
    public static function create(Adherent $recipient, int $newAdherents, int $newSubscribedAdherents): self
    {
        return new self(
            Uuid::v4(),
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
