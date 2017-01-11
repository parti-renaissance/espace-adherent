<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountActivationMessage extends MailjetMessage
{
    public static function createFromAdherent(Adherent $adherent, string $confirmationLink): self
    {
        $message = new static(
            Uuid::uuid4(),
            '54665',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Finalisez votre inscription au mouvement EnMarche !'
        );
        $message->setVar('target_firstname', $adherent->getFirstName());
        $message->setVar('confirmation_link', $confirmationLink);

        return $message;
    }
}
