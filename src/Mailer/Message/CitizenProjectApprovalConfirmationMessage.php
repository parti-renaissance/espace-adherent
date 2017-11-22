<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class CitizenProjectApprovalConfirmationMessage extends Message
{
    public static function create(Adherent $administrator, CitizenProject $citizenProject): self
    {
        $message = new self(
            Uuid::uuid4(),
            '244444 ',
            $administrator->getEmailAddress(),
            $administrator->getFullName(),
            'Votre projet citoyen a été publié. À vous de jouer !'
        );

        $message->setVar('target_firstname', self::escape($administrator->getFirstName()));
        $message->setVar('citizen_project_name', self::escape($citizenProject->getName()));

        return $message;
    }
}
