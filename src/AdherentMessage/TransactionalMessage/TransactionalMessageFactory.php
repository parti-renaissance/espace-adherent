<?php

declare(strict_types=1);

namespace App\AdherentMessage\TransactionalMessage;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailer\Message\Message;
use App\Mailer\Message\Renaissance\AdherentMessage\DefaultUnlayerRenaissanceMessage;
use App\Mailer\Message\Renaissance\AdherentMessage\StatutoryRenaissanceMessage;

class TransactionalMessageFactory
{
    public static function createFromAdherentMessage(AdherentMessageInterface $message, array $recipients = []): Message
    {
        if ($message->isStatutory()) {
            return StatutoryRenaissanceMessage::create($message, $recipients);
        }

        return DefaultUnlayerRenaissanceMessage::create($message, $recipients);
    }
}
