<?php

namespace App\AdherentMessage\TransactionalMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\TransactionalMessageInterface;
use App\Mailer\Message\Message;
use App\Mailer\Message\Renaissance\AdherentMessage\DefaultUnlayerRenaissanceMessage;
use App\Mailer\Message\Renaissance\AdherentMessage\StatutoryRenaissanceMessage;

class TransactionalMessageFactory
{
    public static function createFromAdherentMessage(AdherentMessageInterface $message, array $recipients = []): Message
    {
        if ($message instanceof TransactionalMessageInterface) {
            if (AdherentMessageTypeEnum::STATUTORY === $message->getType()) {
                return StatutoryRenaissanceMessage::create($message, $recipients);
            }

            return DefaultUnlayerRenaissanceMessage::create($message, $recipients);
        }

        throw new \RuntimeException(\sprintf('Unknown transactional adherent message "%s"', $message::class));
    }
}
