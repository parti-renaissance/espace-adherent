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
            'first_name' => self::escape($adherent->getFirstName()),
            'citizen_project_name' => self::escape($citizenProject->getName()),
            'citizen_project_slug' => self::escape($citizenProject->getSlug()),
            'creator_first_name' => self::escape($creator->getFirstName()),
            'creator_last_name' => self::escape($creator->getLastName()),
        ];
    }
}
