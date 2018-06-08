<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class CitizenProjectCreationConfirmationMessage extends Message
{
    public static function create(Adherent $creator, CitizenProject $citizenProject): self
    {
        $message = new self(
            Uuid::uuid4(),
            $creator->getEmailAddress(),
            $creator->getFullName(),
            static::getTemplateVars($citizenProject, $creator)
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        return $message;
    }

    private static function getTemplateVars(CitizenProject $citizenProject, Adherent $creator): array
    {
        return [
            'first_name' => self::escape($creator->getFirstName()),
            'citizen_project_name' => self::escape($citizenProject->getName()),
        ];
    }
}
