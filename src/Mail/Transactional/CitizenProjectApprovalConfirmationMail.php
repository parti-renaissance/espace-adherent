<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\CitizenProject;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\RecipientInterface;

final class CitizenProjectApprovalConfirmationMail extends AbstractCitizenProjectMail
{
    public const SUBJECT = 'Votre projet citoyen a été publié. À vous de jouer !';

    public static function createRecipientFrom(CitizenProject $citizenProject): RecipientInterface
    {
        return self::createRecipientFromAdherent($citizenProject->getCreator(), [
            'citizen_project_name' => StringCleaner::htmlspecialchars($citizenProject->getName()),
            'target_firstname' => StringCleaner::htmlspecialchars($citizenProject->getCreator()->getFirstName()),
        ]);
    }
}
