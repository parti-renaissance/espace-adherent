<?php

declare(strict_types=1);

namespace App\Adhesion\Request;

use App\Entity\Adherent;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCommunicationRequest
{
    #[AssertPhoneNumber]
    #[Assert\Expression('not this.acceptSms or this.phone', message: "Vous avez accepté de recevoir des informations du parti par SMS ou téléphone, cependant, vous n'avez pas précisé votre numéro de téléphone.")]
    public ?PhoneNumber $phone = null;

    public bool $acceptSms = false;

    public bool $acceptEmail = false;

    public static function fromAdherent(Adherent $adherent): self
    {
        $request = new self();
        $request->phone = $adherent->getPhone();

        return $request;
    }
}
