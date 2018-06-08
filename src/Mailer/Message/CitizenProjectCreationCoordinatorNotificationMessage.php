<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class CitizenProjectCreationCoordinatorNotificationMessage extends Message
{
    public static function create(
        Adherent $coordinator,
        CitizenProject $citizenProject,
        Adherent $creator,
        string $citizenProjectUrl
    ): self {
        $message = new self(
            Uuid::uuid4(),
            $coordinator->getEmailAddress(),
            $coordinator->getFullName(),
            static::getTemplateVars($citizenProject, $creator, $coordinator, $citizenProjectUrl)
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        return $message;
    }

    private static function getTemplateVars(
        CitizenProject $citizenProject,
        Adherent $creator,
        Adherent $coordinator,
        string $citizenProjectUrl
    ): array {
        return [
            'first_name' => self::escape($coordinator->getFirstName()),
            'citizen_project_host_first_name' => self::escape($citizenProject->getName()),
            'citizen_project_host_last_name' => self::escape($creator->getFirstName()),
            'citizen_project_name' => self::escape($creator->getLastName()),
            'citizen_project_url' => $citizenProjectUrl,
        ];
    }
}
