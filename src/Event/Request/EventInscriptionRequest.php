<?php

namespace App\Event\Request;

use libphonenumber\PhoneNumber;

class EventInscriptionRequest
{
    public ?string $email = null;
    public ?string $civility = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?\DateTimeInterface $birthdate = null;
    public ?PhoneNumber $phone = null;
    public ?string $postalCode = null;
    public bool $allowNotifications = false;
    public ?string $utmSource = null;
    public ?string $utmCampaign = null;
}
