<?php

namespace App\Adherent\Campus;

use App\Entity\Adherent;
use Ramsey\Uuid\UuidInterface;

class AdherentValueObject
{
    public UuidInterface $uuid;
    public ?\DateTimeInterface $subscriptionDate;
    public ?string $civility;
    public string $fname;
    public string $lname;
    public string $emailAddress;
    public ?string $phone;
    public ?\DateTimeInterface $birthdate;
    public bool $under35;
    public ?string $postalCode;
    public ?string $countryCode;

    public static function createFromAdherent(Adherent $adherent): self
    {
        $valueObject = new self();

        $valueObject->uuid = $adherent->getUuid();
        $valueObject->subscriptionDate = $adherent->getRegisteredAt();
        $valueObject->civility = $adherent->getGender();
        $valueObject->fname = $adherent->getFirstName();
        $valueObject->lname = $adherent->getLastName();
        $valueObject->emailAddress = $adherent->getEmailAddress();
        $valueObject->phone = $adherent->getPhone();
        $valueObject->birthdate = $adherent->getBirthdate();
        $valueObject->under35 = 35 > $adherent->getAge();
        $valueObject->postalCode = $adherent->getPostalCode();
        $valueObject->countryCode = $adherent->getCountry();

        return $valueObject;
    }
}
