<?php

namespace App\Mailer\Message;

use App\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class CitizenProjectApprovalConfirmationMessage extends Message
{
    public static function create(CitizenProject $citizenProject): self
    {
        $message = new self(
            Uuid::uuid4(),
            $citizenProject->getCreator() ? $citizenProject->getCreator()->getEmailAddress() : '',
            $citizenProject->getCreator() ? $citizenProject->getCreator()->getFullName() : '',
            'Votre projet citoyen a été publié. À vous de jouer !',
            [
                'citizen_project_name' => self::escape($citizenProject->getName()),
            ],
            [
                'target_firstname' => self::escape($citizenProject->getCreator() ? $citizenProject->getCreator()->getFirstName() : ''),
            ]
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        return $message;
    }
}
