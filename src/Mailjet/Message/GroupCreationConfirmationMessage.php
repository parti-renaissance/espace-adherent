<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class GroupCreationConfirmationMessage extends MailjetMessage
{
    public static function create(Adherent $adherent): self
    {
        $message = new self(
            Uuid::uuid4(),
            '224426',
            $adherent->getEmailAddress(),
            self::fixMailjetParsing($adherent->getFullName()),
            'Votre équipe MOOC est en attente de validation'
        );

        $message->setVar('target_firstname', self::escape($adherent->getFirstName()));

        return $message;
    }
}
