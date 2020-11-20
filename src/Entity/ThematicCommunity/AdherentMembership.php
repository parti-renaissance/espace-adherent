<?php

namespace App\Entity\ThematicCommunity;

use App\Entity\PostAddress;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;

/**
 * @ORM\Entity
 * @ORM\Table(name="thematic_community_membership_adherent")
 */
class AdherentMembership extends ThematicCommunityMembership
{
    public function getFirstName(): ?string
    {
        return $this->adherent ? $this->adherent->getFirstName() : null;
    }

    public function getLastName(): ?string
    {
        return $this->adherent ? $this->adherent->getLastName() : null;
    }

    public function getEmail(): ?string
    {
        return $this->adherent ? $this->adherent->getEmailAddress() : null;
    }

    public function getGender(): ?string
    {
        return $this->adherent ? $this->adherent->getGender() : null;
    }

    public function getCustomGender(): ?string
    {
        return $this->adherent ? $this->adherent->getCustomGender() : null;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->adherent ? $this->adherent->getBirthDate() : null;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->adherent ? $this->adherent->getPhone() : null;
    }

    public function getPosition(): ?string
    {
        return $this->adherent ? $this->adherent->getPosition() : null;
    }

    public function getPostAddress(): ?PostAddress
    {
        return $this->adherent->getPostAddressModel();
    }

    public function getCityName(): ?string
    {
        return $this->adherent ? $this->adherent->getCityName() : null;
    }

    public function getPostalCode(): ?string
    {
        return $this->adherent ? $this->adherent->getPostalCode() : null;
    }

    public function hasSmsSubscriptionType(): bool
    {
        return $this->adherent ? $this->adherent->hasSmsSubscriptionType() : false;
    }

    public function hasEmailSubscriptionType(): bool
    {
        return $this->adherent ? $this->adherent->hasSubscriptionType(SubscriptionTypeEnum::THEMATIC_COMMUNITY_EMAIL) : false;
    }

    public function isCertified(): bool
    {
        return $this->adherent ? $this->adherent->isCertified() : false;
    }
}
