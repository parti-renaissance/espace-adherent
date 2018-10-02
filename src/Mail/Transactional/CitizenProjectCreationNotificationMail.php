<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\ChunkableMailInterface;

final class CitizenProjectCreationNotificationMail extends AbstractCitizenProjectMail implements ChunkableMailInterface
{
    public const SUBJECT = 'Un projet citoyen se lance près de chez vous !';

    public static function createRecipients(array $adherents): array
    {
        return array_map(
            function (Adherent $adherent) {
                return self::createRecipientFromAdherent(
                    $adherent,
                    ['target_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName())]
                );
            },
            $adherents
        );
    }

    public static function createTemplateVars(CitizenProject $citizenProject, Adherent $creator): array
    {
        return [
            'citizen_project_name' => StringCleaner::htmlspecialchars($citizenProject->getName()),
            'citizen_project_host_firstname' => StringCleaner::htmlspecialchars($creator->getFirstName()),
            'citizen_project_host_lastname' => StringCleaner::htmlspecialchars($creator->getLastName()),
            'citizen_project_slug' => StringCleaner::htmlspecialchars($citizenProject->getSlug()),
        ];
    }
}
