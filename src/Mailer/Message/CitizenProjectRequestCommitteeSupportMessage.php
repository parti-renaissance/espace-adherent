<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

class CitizenProjectRequestCommitteeSupportMessage extends Message
{
    public static function create(CitizenProject $citizenProject, ?Adherent $committeeHost, string $validationUrl): self
    {
        $message = new self(
            Uuid::uuid4(),
            '263222',
            $committeeHost->getEmailAddress(),
            $committeeHost->getFullName(),
            'Un projet citoyen se lance près de chez vous !'
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        $message->setVar('target_firstname', self::escape($committeeHost->getFirstName() ?? ''));
        $message->setVar('citizen_project_name', self::escape($citizenProject->getName()));
        $message->setVar('citizen_project_host_firstname', self::escape($citizenProject->getCreator() ? $citizenProject->getCreator()->getFirstName() : ''));
        $message->setVar('citizen_project_host_lastname', self::escape($citizenProject->getCreator() ? $citizenProject->getCreator()->getLastName() : ''));
        $message->setVar('citizen_project_slug', self::escape($citizenProject->getSlug()));
        $message->setVar('validation_url', self::escape($validationUrl));

        return $message;
    }
}
