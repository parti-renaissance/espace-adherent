<?php

namespace App\Coalition;

use App\Entity\Adherent;
use App\Entity\Coalition\CauseFollower;

class CoalitionContactValueObject
{
    private $email;
    private $firstName;
    private $lastName;
    private $gender;
    private $city;
    private $source;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public static function createFromCauseFollower(CauseFollower $causeFollower): self
    {
        $object = new self();

        $object->email = $causeFollower->getEmailAddress();
        $object->firstName = $causeFollower->getFirstName();
        $object->source = ContactSourceEnum::FOLLOWER;

        return $object;
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        $object = new self();

        $object->email = $adherent->getEmailAddress();
        $object->firstName = $adherent->getFirstName();
        $object->lastName = $adherent->getLastName();
        $object->gender = $adherent->getGender();
        $object->source = $adherent->isCoalitionsCguAccepted() ? ContactSourceEnum::COALITION_USER : ContactSourceEnum::ADHERENT;

        return $object;
    }
}
