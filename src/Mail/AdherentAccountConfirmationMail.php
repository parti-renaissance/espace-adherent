<?php

namespace AppBundle\Mail;

use AppBundle\Entity\Adherent;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class AdherentAccountConfirmationMail extends TransactionalMail
{
    use AdherentMailTrait;

    public static function createRecipientFor(Adherent $adherent, int $adherentsCount = 0): RecipientInterface
    {
        return self::createRecipientFromAdherent($adherent, [
            'adherents_count' => $adherentsCount,
            'first_name' => $adherent->getFirstName(),
        ]);
    }
}
