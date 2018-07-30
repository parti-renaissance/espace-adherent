<?php

namespace AppBundle\Mail;

use AppBundle\Entity\Adherent;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class AdherentAccountActivationMail extends TransactionalMail
{
    use AdherentMailTrait;

    /*
     * Not inheriting or overriding or implementing anything here. This is just about transforming model to an instance
     * of RecipientInterface.
     */
    public static function createRecipientFor(Adherent $adherent, string $activationUrl): RecipientInterface
    {
        return self::createRecipientFromAdherent($adherent, [
            'first_name' => $adherent->getFirstName(),
            'activation_url' => $activationUrl,
        ]);
    }
}
