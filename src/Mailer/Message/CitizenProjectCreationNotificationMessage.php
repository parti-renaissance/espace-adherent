<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class CitizenProjectCreationNotificationMessage extends Message
{
    public static function create(Adherent $adherent, CitizenProject $citizenProject, string $citizenProjectsUrl): self
    {
        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            static::getTemplateVars($adherent, $citizenProject, $citizenProjectsUrl)
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        return $message;
    }

    private static function getTemplateVars(
        Adherent $adherent,
        CitizenProject $citizenProject,
        string $citizenProjectsUrl
    ): array {
        return [
            'first_name' => self::escape($adherent->getFirstName()),
            'citizen_project_list' => self::escape($citizenProject->getName()),
            'all_citizen_projects_url' => $citizenProjectsUrl,
        ];
    }
}
