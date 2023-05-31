<?php

namespace App\Event;

use App\Entity\Adherent;
use App\Validator\Recaptcha as AssertRecaptcha;

/**
 * @AssertRecaptcha(api="friendly_captcha")
 */
class RenaissanceEventInvitation extends BaseEventInvitation
{
    public static function createFromAdherent(?Adherent $adherent, ?string $recaptchaAnswer): self
    {
        $dto = new self();

        if ($adherent) {
            $dto->lastName = $adherent->getLastName();
            $dto->firstName = $adherent->getFirstName();
            $dto->email = $adherent->getEmailAddress();
        }

        $dto->setRecaptcha($recaptchaAnswer);

        return $dto;
    }
}
