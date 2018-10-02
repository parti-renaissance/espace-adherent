<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Utils\StringCleaner;

final class CitizenProjectCreationCoordinatorNotificationMail extends AbstractCitizenProjectMail
{
    public const SUBJECT = '[Projet citoyen] Un nouveau projet citoyen attend votre validation !';

    public static function createRecipients(array $adherents): array
    {
        return array_map(function (Adherent $adherent) {
            return self::createRecipientFromAdherent(
                $adherent,
                ['target_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName())]
            );
        }, $adherents);
    }

    public static function createTemplateVars(CitizenProject $citizenProject, Adherent $creator, string $link): array
    {
        return [
            'citizen_project_name' => StringCleaner::htmlspecialchars($citizenProject->getName()),
            'citizen_project_host_firstname' => StringCleaner::htmlspecialchars($creator->getFirstName()),
            'citizen_project_host_lastname' => StringCleaner::htmlspecialchars($creator->getLastName()),
            'coordinator_space_url' => $link,
        ];
    }
}
