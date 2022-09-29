<?php

namespace App\AdherentMessage\TransactionalMessage;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\ReferentInstancesMessage;
use App\Mailer\Message\Message;
use App\Mailer\Message\Renaissance\AdherentMessage\RenaissanceReferentToInstancesMembershipMessage;

class TransactionalMessageFactory
{
    public static function createFromAdherentMessage(AdherentMessageInterface $message, array $recipients = []): Message
    {
        switch (\get_class($message)) {
            case ReferentInstancesMessage::class:
                return RenaissanceReferentToInstancesMembershipMessage::create($message, $recipients);
        }

        throw new \RuntimeException(sprintf('Unknown transactional adherent message "%s"', \get_class($message)));
    }
}
