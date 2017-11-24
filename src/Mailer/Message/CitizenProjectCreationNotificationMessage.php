<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class CitizenProjectCreationNotificationMessage extends Message
{
    public static function create(Adherent $adherent): self
    {
        $message = new self(
            Uuid::uuid4(),
            '666666',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Un nouveau projet citoyen près de chez vous !'
        );

        $message->setVar('target_firstname', self::escape($adherent->getFirstName()));

        return $message;
    }
}
