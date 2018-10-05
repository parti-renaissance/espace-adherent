<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\RecipientInterface;

class TurnkeyProjectApprovalConfirmationMail extends AbstractCitizenProjectMail
{
    public const SUBJECT = 'Votre projet citoyen a été publié. À vous de jouer !';

    public static function createRecipientFor(Adherent $adherent): RecipientInterface
    {
        return self::createRecipientFromAdherent($adherent, [
            'target_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName()),
        ]);
    }

    public static function createTemplateVarsFrom(CitizenProject $citizenProject, string $kitUrl): array
    {
        return [
            'citizen_project_name' => StringCleaner::htmlspecialchars($citizenProject->getName()),
            'kit_url' => $kitUrl,
        ];
    }
}

