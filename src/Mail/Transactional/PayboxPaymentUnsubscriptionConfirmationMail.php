<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class PayboxPaymentUnsubscriptionConfirmationMail extends TransactionalMail
{
    use AdherentMailTrait;

    public const SUBJECT = 'Votre don mensuel a bien été annulé.';

    public static function createRecipient(Adherent $adherent): RecipientInterface
    {
        return self::createRecipientFromAdherent($adherent, [
           'target_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName()),
        ]);
    }
}
