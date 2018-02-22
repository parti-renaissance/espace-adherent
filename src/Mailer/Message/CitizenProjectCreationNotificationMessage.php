<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class CitizenProjectCreationNotificationMessage extends Message
{
    public static function create(Adherent $adherent, CitizenProject $citizenProject, Adherent $creator): self
    {
        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            static::getTemplateVars($adherent, $citizenProject, $creator)
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        return $message;
    }

    private static function getTemplateVars(
        Adherent $adherent,
        CitizenProject $citizenProject,
        Adherent $creator
    ): array {
        return [
            'citizen_project_name' => self::escape($citizenProject->getName()),
            'citizen_project_slug' => self::escape($citizenProject->getSlug()),
            'citizen_project_host_firstname' => self::escape($creator->getFirstName()),
            'citizen_project_host_lastname' => self::escape($creator->getLastName()),
            'target_firstname' => self::escape($adherent->getFirstName()),
        ];
    }
}
