<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class CommitteeApprovalReferentMail extends TransactionalMail
{
    use AdherentMailTrait;

    public const SUBJECT = 'Un comité vient d\'être approuvé';

    public static function createRecipient(Adherent $adherent): RecipientInterface
    {
        return self::createRecipientFromAdherent(
            $adherent,
            ['prenom' => StringCleaner::htmlspecialchars($adherent->getFirstName())]
        );
    }

    public static function createTemplateVars(Committee $committee, Adherent $animator, string $link): array
    {
        return [
            'committee_name' => $committee->getName(),
            'committee_city' => $committee->getCityName(),
            'animator_firstname' => $animator->getFirstName(),
            'animator_contact_link' => $link,
        ];
    }
}
