<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class CommitteeCreationConfirmationMessage extends MailjetMessage
{
    public static function create(Adherent $adherent, string $city): self
    {
        $message = new self(
            Uuid::uuid4(),
            '54689',
            $adherent->getEmailAddress(),
            self::fixMailjetParsing($adherent->getFullName()),
            'Votre comité sera bientôt en ligne'
        );

        $message->setVar('target_firstname', self::escape($adherent->getFirstName()));
        $message->setVar('committee_city', $city);

        return $message;
    }
}
