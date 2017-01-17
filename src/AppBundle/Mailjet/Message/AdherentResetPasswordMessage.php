<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentResetPasswordMessage extends MailjetMessage
{
    public static function createFromAdherent(Adherent $adherent, string $resetPasswordLink): self
    {
        $message = new static(
            Uuid::uuid4(),
            '54665',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Réinitialisez votre mot de passe pour vous connecter à En Marche !'
        );
        $message->setVar('target_firstname', $adherent->getFirstName());
        $message->setVar('reset_password_link', $resetPasswordLink);

        return $message;
    }
}
