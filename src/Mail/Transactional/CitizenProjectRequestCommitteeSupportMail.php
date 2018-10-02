<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\RecipientInterface;

final class CitizenProjectRequestCommitteeSupportMail extends AbstractCitizenProjectMail
{
    public const SUBJECT = 'Un projet citoyen a besoin du soutien de votre comité !';

    public static function createRecipient(Adherent $adherent): RecipientInterface
    {
        return self::createRecipientFromAdherent(
            $adherent,
            ['target_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName())]
        );
    }

    public static function createTemplateVars(CitizenProject $citizenProject, string $link): array
    {
        return [
            'citizen_project_name' => StringCleaner::htmlspecialchars($citizenProject->getName()),
            'citizen_project_host_firstname' => StringCleaner::htmlspecialchars($citizenProject->getCreator() ? $citizenProject->getCreator()->getFirstName() : ''),
            'citizen_project_host_lastname' => StringCleaner::htmlspecialchars($citizenProject->getCreator() ? $citizenProject->getCreator()->getLastName() : ''),
            'validation_url' => $link,
        ];
    }
}
