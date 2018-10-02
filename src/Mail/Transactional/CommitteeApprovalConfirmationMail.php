<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class CommitteeApprovalConfirmationMail extends TransactionalMail
{
    use AdherentMailTrait;

    public const SUBJECT = 'Votre comité est validé, à vous de jouer';

    public static function createRecipientFrom(Adherent $adherent): RecipientInterface
    {
        return self::createRecipientFromAdherent(
            $adherent,
            ['animator_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName())]
        );
    }

    public static function createTemplateVars(Committee $committee, string $link): array
    {
        return [
            'committee_city' => $committee->getCityName(),
            'committee_url' => $link,
        ];
    }
}
