<?php

namespace App\AdherentMessage\TransactionalMessage;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\ReferentInstancesMessage;
use App\Entity\AdherentMessage\TransactionalMessageInterface;
use App\Mailer\Message\Message;
use App\Mailer\Message\Renaissance\AdherentMessage\DefaultUnlayerRenaissanceMessage;
use App\Mailer\Message\Renaissance\AdherentMessage\RenaissanceReferentToInstancesMembershipMessage;

class TransactionalMessageFactory
{
    public static function createFromAdherentMessage(AdherentMessageInterface $message, array $recipients = []): Message
    {
        switch ($message::class) {
            case ReferentInstancesMessage::class:
                return RenaissanceReferentToInstancesMembershipMessage::create($message, $recipients);
        }

        if ($message instanceof TransactionalMessageInterface) {
            return DefaultUnlayerRenaissanceMessage::create($message, $recipients);
        }

        throw new \RuntimeException(sprintf('Unknown transactional adherent message "%s"', $message::class));
    }
}
