<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class AdherentTerminateMembershipMail extends TransactionalMail
{
    use AdherentMailTrait;

    public static function createRecipient(Adherent $adherent): RecipientInterface
    {
        return self::createRecipientFromAdherent($adherent, [
            'target_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName()),
        ]);
    }
}
