<?php

namespace App\Coalition;

use App\Entity\Adherent;
use App\Entity\Coalition\CauseFollower;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;

class CoalitionMemberValueObject
{
    private $email;
    private $firstName;
    private $lastName;
    private $gender;
    private $zone;
    private $postAddress;
    private $source;
    private $isAdherent;
    private $causeSubscription = false;
    private $coalitionSubscription = false;

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

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function getPostAddress(): ?PostAddress
    {
        return $this->postAddress;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function isAdherent(): bool
    {
        return $this->isAdherent;
    }

    public function hasCauseSubscription(): bool
    {
        return $this->causeSubscription;
    }

    public function hasCoalitionSubscription(): bool
    {
        return $this->coalitionSubscription;
    }

    public static function createFromCauseFollower(CauseFollower $causeFollower): self
    {
        $object = new self();

        $object->isAdherent = false;
        $object->email = $causeFollower->getEmailAddress();
        $object->firstName = $causeFollower->getFirstName();
        $object->source = ContactSourceEnum::FOLLOWER;
        $object->zone = $causeFollower->getZone();
        if ($causeFollower->isCguAccepted()) {
            $object->causeSubscription = $causeFollower->hasCauseSubscription();
            $object->coalitionSubscription = $causeFollower->hasCoalitionSubscription();
        }

        return $object;
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        $object = new self();

        $object->isAdherent = true;
        $object->email = $adherent->getEmailAddress();
        $object->firstName = $adherent->getFirstName();
        $object->lastName = $adherent->getLastName();
        $object->gender = $adherent->getGender();
        $object->source = $adherent->isCoalitionUser() ? ContactSourceEnum::COALITION_USER : ContactSourceEnum::ADHERENT;
        $object->postAddress = $adherent->getPostAddress();
        if ($adherent->isCoalitionsCguAccepted()) {
            $object->causeSubscription = $adherent->isCauseSubscription();
            $object->coalitionSubscription = $adherent->isCoalitionSubscription();
        }

        return $object;
    }
}
