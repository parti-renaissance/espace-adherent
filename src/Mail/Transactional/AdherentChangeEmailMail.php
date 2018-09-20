<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class AdherentChangeEmailMail extends TransactionalMail
{
    use AdherentMailTrait;

    public static function createRecipientFor(Adherent $adherent, string $confirmationLink): RecipientInterface
    {
        return self::createRecipientFromAdherent($adherent, [
            'first_name' => StringCleaner::htmlspecialchars($adherent->getFirstName()),
            'activation_link' => $confirmationLink,
        ]);
    }
}
