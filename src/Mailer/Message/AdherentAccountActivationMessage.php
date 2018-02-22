<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountActivationMessage extends Message
{
    public static function create(Adherent $adherent, string $activationUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            static::getTemplateVars($adherent, $activationUrl)
        );
    }

    private static function getTemplateVars(Adherent $adherent, string $activationUrl): array
    {
        return [
            'first_name' => self::escape($adherent->getFirstName()),
            'activation_url' => $activationUrl,
        ];
    }
}
