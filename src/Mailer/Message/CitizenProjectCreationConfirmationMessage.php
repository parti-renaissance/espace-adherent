<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class CitizenProjectCreationConfirmationMessage extends Message
{
    public static function create(Adherent $adherent): self
    {
        $message = new self(
            Uuid::uuid4(),
            '224426',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre projet citoyen sera bientôt en ligne'
        );

        $message->setVar('target_firstname', self::escape($adherent->getFirstName()));

        return $message;
    }
}
