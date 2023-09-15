<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use App\Entity\Administrator;
use Ramsey\Uuid\Uuid;

class RenaissanceDeclaredMandateNotificationMessage extends AbstractRenaissanceMessage
{
    public static function createForAdministrator(Administrator $administrator, array $mandates, string $buttonUrl): self
    {
        return self::create($administrator->getEmailAddress(), $mandates, $buttonUrl);
    }

    public static function createForAdherent(Adherent $adherent, array $mandates, string $buttonUrl): self
    {
        return self::create($adherent->getEmailAddress(), $mandates, $buttonUrl);
    }

    private static function create(string $recipientEmail, array $mandates, string $buttonUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $recipientEmail,
            null,
            'Nouvelles déclarations de mandats',
            [
                'mandates' => $mandates,
                'mandates_count' => \count($mandates),
                'button_url' => $buttonUrl,
            ]
        );
    }
}
