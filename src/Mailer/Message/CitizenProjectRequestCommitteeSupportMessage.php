<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use Ramsey\Uuid\Uuid;

class CitizenProjectRequestCommitteeSupportMessage extends Message
{
    public static function create(CitizenProject $citizenProject, Adherent $creator, ?Adherent $committeeHost): self
    {
        $message = new self(
            Uuid::uuid4(),
            '263222',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Un projet citoyen se lance près de chez vous !'
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        $message->setVar('target_firstname', self::escape($committeeHost->getFirstName() ?? ''));
        $message->setVar('citizen_project_name', self::escape($adherent->getFirstName()));
        $message->setVar('citizen_project_host_firstname', self::escape($creator->getFirstName()));
        $message->setVar('citizen_project_host_lastname', self::escape($creator->getLastName()));
        $message->setVar('citizen_project_slug', self::escape($citizenProject->getSlug()));

        return $message;
    }
}
