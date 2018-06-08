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
            ['recipient_first_name' => self::escape($committeeSupervisor->getFirstName())]
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        return $message;
    }

    private static function getTemplateVars(CitizenProject $citizenProject, string $validationUrl): array
    {
        $creator = $citizenProject->getCreator();

        return [
            'citizen_project_name' => self::escape($citizenProject->getName()),
            'citizen_project_host_first_name' => self::escape($creator ? $creator->getFirstName() : ''),
            'citizen_project_host_last_name' => self::escape($creator ? $creator->getLastName() : ''),
            'citizen_project_committee_support_url' => self::escape($validationUrl),
        ];
    }
}
