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
            static::getTemplateVars($citizenProject, $committeeSupervisor, $validationUrl)
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
            'first_name' => self::escape($committeeSupervisor->getFirstName() ?? ''),
            'citizen_project_name' => self::escape($citizenProject->getName()),
            'creator_first_name' => self::escape($creator ? $creator->getFirstName() : ''),
            'creator_last_name' => self::escape($creator ? $creator->getLastName() : ''),
            'validation_url' => self::escape($validationUrl),
        ];
    }
}
