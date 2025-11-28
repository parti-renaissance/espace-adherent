<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use Ramsey\Uuid\Uuid;

class RenaissanceDeclaredMandateNotificationMessage extends AbstractRenaissanceMessage
{
    public static function create(array $recipients, array $mandates, string $buttonUrl): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $message = new self(
            Uuid::uuid4(),
            reset($recipients),
            null,
            'Nouvelles déclarations de mandats',
            [
                'mandates' => $mandates,
                'mandates_count' => \count($mandates),
                'button_url' => $buttonUrl,
            ]
        );

        foreach ($recipients as $recipient) {
            $message->addRecipient($recipient);
        }

        return $message;
    }
}
