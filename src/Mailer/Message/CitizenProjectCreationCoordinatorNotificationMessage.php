<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class CitizenProjectCreationCoordinatorNotificationMessage extends Message
{
    public static function create(
        CitizenProject $citizenProject,
        Adherent $creator,
        Adherent $coordinator,
        string $coordinatorSpaceUrl
    ): self {
        $message = new self(
            Uuid::uuid4(),
            $coordinator->getEmailAddress(),
            $coordinator->getFullName(),
            static::getTemplateVars($citizenProject, $creator, $coordinator, $coordinatorSpaceUrl)
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        return $message;
    }

    private static function getTemplateVars(
        CitizenProject $citizenProject,
        Adherent $creator,
        Adherent $coordinator,
        string $coordinatorSpaceUrl
    ): array {
        return [
            'citizen_project_name' => self::escape($citizenProject->getName()),
            'citizen_project_host_firstname' => self::escape($creator->getFirstName()),
            'citizen_project_host_lastname' => self::escape($creator->getLastName()),
            'coordinator_space_url' => $coordinatorSpaceUrl,
            'target_firstname' => self::escape($coordinator->getFirstName()),
        ];
    }
}
