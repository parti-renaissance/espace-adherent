<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Administrator;
use Ramsey\Uuid\Uuid;

class RenaissanceDeclaredMandateNotificationMessage extends AbstractRenaissanceMessage
{
    public static function createForAdministrator(Administrator $administrator, array $mandates): self
    {
        return self::create($administrator->getEmailAddress(), $mandates);
    }

    private static function create(string $recipientEmail, array $mandates): self
    {
        return new self(
            Uuid::uuid4(),
            $recipientEmail,
            null,
            'Nouvelles déclarations de mandats',
            [
                'mandates' => $mandates,
            ]
        );
    }
}
