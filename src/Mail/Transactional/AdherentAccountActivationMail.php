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

    public static function createRecipient(Adherent $adherent, string $activationUrl): RecipientInterface
    {
        return self::createRecipientFromAdherent($adherent, [
            'first_name' => StringCleaner::htmlspecialchars($adherent->getFirstName()),
            'activation_link' => $activationUrl,
        ]);
    }
}
