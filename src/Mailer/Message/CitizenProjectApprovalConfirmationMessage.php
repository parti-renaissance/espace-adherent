<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class CitizenProjectApprovalConfirmationMessage extends Message
{
    public static function create(CitizenProject $citizenProject): self
    {
        $message = new self(
            Uuid::uuid4(),
            '244444',
            $citizenProject->getCreator() ? $citizenProject->getCreator()->getEmailAddress() : '',
            $citizenProject->getCreator() ? $citizenProject->getCreator()->getFullName() : '',
            'Votre projet citoyen a été publié. À vous de jouer !'
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        $message->setVar('target_firstname', self::escape($citizenProject->getCreator() ? $citizenProject->getCreator()->getFirstName() : ''));
        $message->setVar('citizen_project_name', self::escape($citizenProject->getName()));

        return $message;
    }
}
