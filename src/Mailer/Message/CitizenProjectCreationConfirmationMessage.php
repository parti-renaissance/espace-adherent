<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class CitizenProjectCreationConfirmationMessage extends Message
{
    public static function create(Adherent $adherent, CitizenProject $citizenProject, string $projectUrl): self
    {
        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Nous avons bien reçu votre demande de création de projet citoyen !',
            [
                'target_firstname' => self::escape($adherent->getFirstName()),
                'citizen_project_name' => self::escape($citizenProject->getName()),
                'project_link' => $projectUrl,
            ]
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        return $message;
    }
}
