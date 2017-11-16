<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class CitizenProjectApprovalConfirmationMessage extends Message
{
    public static function create(Adherent $administrator): self
    {
        $message = new self(
            Uuid::uuid4(),
            '244444',
            $administrator->getEmailAddress(),
            $administrator->getFullName(),
            'Votre projet citoyen est validé, à vous de jouer'
        );

        $message->setVar('target_firstname', self::escape($administrator->getFirstName()));

        return $message;
    }
}
