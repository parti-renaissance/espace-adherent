<?php

namespace App\Entity\ThematicCommunity;

use App\Entity\PostAddress;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;

#[ORM\Table(name: 'thematic_community_membership_adherent')]
#[ORM\Entity]
class AdherentMembership extends ThematicCommunityMembership
{
    public function getFirstName(): ?string
    {
        return $this->adherent?->getFirstName();
    }

    public function getLastName(): ?string
    {
        return $this->adherent?->getLastName();
    }

    public function getEmail(): ?string
    {
        return $this->adherent?->getEmailAddress();
    }

    public function getGender(): ?string
    {
        return $this->adherent?->getGender();
    }

    public function getCustomGender(): ?string
    {
        return $this->adherent?->getCustomGender();
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->adherent?->getBirthDate();
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->adherent?->getPhone();
    }

    public function getPosition(): ?string
    {
        return $this->adherent?->getPosition();
    }

    public function getPostAddress(): ?PostAddress
    {
        return $this->adherent->getPostAddress();
    }

    public function getCityName(): ?string
    {
        return $this->adherent?->getCityName();
    }

    public function getPostalCode(): ?string
    {
        return $this->adherent?->getPostalCode();
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
