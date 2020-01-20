<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class TurnkeyProjectApprovalConfirmationMessage extends Message
{
    public static function create(CitizenProject $citizenProject, string $citizenProjectKitUrl): self
    {
        $message = new self(
            Uuid::uuid4(),
            $citizenProject->getCreator() ? $citizenProject->getCreator()->getEmailAddress() : '',
            $citizenProject->getCreator() ? $citizenProject->getCreator()->getFullName() : '',
            'Votre projet citoyen a été publié. À vous de jouer !',
            [
                'citizen_project_name' => self::escape($citizenProject->getName()),
                'kit_url' => $citizenProjectKitUrl,
            ],
            [
                'target_firstname' => self::escape($citizenProject->getCreator() ? $citizenProject->getCreator()->getFirstName() : ''),
            ]
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        return $message;
    }
}
