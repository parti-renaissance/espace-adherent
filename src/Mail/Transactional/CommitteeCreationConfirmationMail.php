<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class CommitteeCreationConfirmationMail extends TransactionalMail
{
    use AdherentMailTrait;

    public const SUBJECT = 'Votre comité sera bientôt en ligne';

    public static function createTemplateVars(Adherent $adherent, Committee $committee): array
    {
        return [
            'committee_city' => $committee->getCityName(),
            'target_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName()),
        ];
    }
}
