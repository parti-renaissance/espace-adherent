<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class CitizenProjectRequestCommitteeSupportMessage extends Message
{
    public static function create(
        CitizenProject $citizenProject,
        Adherent $committeeSupervisor,
        string $validationUrl
    ): self {
        $message = new self(
            Uuid::uuid4(),
            $committeeSupervisor->getEmailAddress(),
            $committeeSupervisor->getFullName(),
            static::getTemplateVars($citizenProject, $validationUrl),
            static::getRecipientVars($committeeSupervisor)
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        return $message;
    }

    private static function getTemplateVars(
        CitizenProject $citizenProject,
        Adherent $committeeSupervisor,
        string $validationUrl
    ): array {
        $creator = $citizenProject->getCreator();

        return [
            'citizen_project_name' => self::escape($citizenProject->getName()),
            'citizen_project_host_firstname' => self::escape($creator ? $creator->getFirstName() : ''),
            'citizen_project_host_lastname' => self::escape($creator ? $creator->getLastName() : ''),
            'target_firstname' => self::escape($committeeSupervisor->getFirstName() ?? ''),
            'validation_url' => self::escape($validationUrl),
        ];
    }

    private static function getRecipientVars(Adherent $committeeSupervisor): array
    {
        return [
        ];
    }
}
