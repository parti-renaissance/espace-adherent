<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountConfirmationMessage extends MailjetMessage
{
    public static function createFromAdherent(Adherent $adherent, int $adherentsCount = 0, int $committeesCount = 0): self
    {
        $message = new static(
            Uuid::uuid4(),
            '54673',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmation de votre inscription au mouvement EnMarche !'
        );

        $message->setVar('target_firstname', $adherent->getFirstName());
        $message->setVar('target_lastname', $adherent->getLastName());
        $message->setVar('adherents_count', $adherentsCount);
        $message->setVar('committees_count', $committeesCount);

        return $message;
    }
}
