<?php

namespace App\Adherent\Campus;

use App\Entity\Adherent;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class AdherentValueObject
{
    /**
     * @Groups({"adherent_campus"})
     */
    public UuidInterface $uuid;

    /**
     * @Groups({"adherent_campus"})
     */
    public ?\DateTimeInterface $subscriptionDate;

    /**
     * @Groups({"adherent_campus"})
     */
    public ?string $civility;

    /**
     * @Groups({"adherent_campus"})
     */
    public string $fname;

    /**
     * @Groups({"adherent_campus"})
     */
    public string $lname;

    /**
     * @Groups({"adherent_campus"})
     */
    public string $emailAddress;

    /**
     * @Groups({"adherent_campus"})
     */
    public ?string $phone;

    /**
     * @Groups({"adherent_campus"})
     */
    public ?\DateTimeInterface $birthdate;

    /**
     * @Groups({"adherent_campus"})
     */
    public bool $under35;

    /**
     * @Groups({"adherent_campus"})
     */
    public ?string $postalCode;

    /**
     * @Groups({"adherent_campus"})
     */
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
