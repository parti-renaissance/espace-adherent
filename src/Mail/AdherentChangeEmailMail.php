<?php

namespace AppBundle\Mail;

use AppBundle\Entity\Adherent;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class AdherentChangeEmailMail extends TransactionalMail
{
    use AdherentMailTrait;

    public static function createRecipientFor(Adherent $adherent, string $confirmationLink): RecipientInterface
    {
        return self::createRecipientFromAdherent($adherent, [
            'first_name' => $adherent->getFirstName(),
            'activation_link' => $confirmationLink,
        ]);
    }
}
