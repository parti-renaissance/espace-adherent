<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class AdherentAccountActivationMail extends TransactionalMail
{
    use AdherentMailTrait;

    /*
     * Not inheriting or overriding or implementing anything here. This is just about transforming model to an instance
     * of RecipientInterface.
     */
    public static function createRecipientFor(Adherent $adherent, string $activationUrl): RecipientInterface
    {
        return self::createRecipientFromAdherent($adherent, [
            'first_name' => StringCleaner::htmlspecialchars($adherent->getFirstName()),
            'activation_link' => $activationUrl,
        ]);
    }
}
