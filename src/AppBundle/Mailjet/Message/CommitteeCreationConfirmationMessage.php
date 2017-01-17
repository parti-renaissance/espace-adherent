<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class CommitteeCreationConfirmationMessage extends MailjetMessage
{
    public static function create(Adherent $adherent, string $city): self
    {
        $message = new static(
            Uuid::uuid4(),
            '54689',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmation de création de votre comité En Marche !'
        );

        $message->setVar('target_firstname', $adherent->getFirstName());
        $message->setVar('committee_city', $city);

        return $message;
    }
}
