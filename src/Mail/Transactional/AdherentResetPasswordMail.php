<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class AdherentResetPasswordMail extends TransactionalMail
{
    use AdherentMailTrait;

    public static function createRecipientFor(Adherent $adherent, string $resetPasswordLink): RecipientInterface
    {
        return self::createRecipientFromAdherent($adherent, [
            'first_name' => StringCleaner::htmlspecialchars($adherent->getFirstName()),
            'reset_link' => $resetPasswordLink,
        ]);
    }
}
