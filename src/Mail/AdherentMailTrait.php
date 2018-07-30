<?php

namespace AppBundle\Mail;

use AppBundle\Entity\Adherent;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;

trait AdherentMailTrait
{
    public static function createRecipientFromAdherent(Adherent $adherent, array $templateVars = []): RecipientInterface
    {
        return new Recipient($adherent->getEmailAddress(), $adherent->getFullName(), $templateVars);
    }
}
