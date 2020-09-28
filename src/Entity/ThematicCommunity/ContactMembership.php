<?php

namespace App\Entity\ThematicCommunity;

use App\Entity\PostAddress;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;

/**
 * @ORM\Entity
 * @ORM\Table(name="thematic_community_membership_contact")
 */
class ContactMembership extends ThematicCommunityMembership
{
    public function getFirstName(): ?string
    {
        return $this->contact ? $this->contact->getFirstName() : null;
    }

    public function setFirstName(string $firstName): void
    {
        $this->contact->setFirstName($firstName);
    }

    public function getLastName(): ?string
    {
        return $this->contact ? $this->contact->getLastName() : null;
    }

    public function setLastName(string $lastName): void
    {
        $this->contact->setLastName($lastName);
    }

    public function getEmail(): ?string
    {
        return $this->contact ? $this->contact->getEmail() : null;
    }

    public function setEmail(string $email): void
    {
        $this->contact->setEmail($email);
    }

    public function getGender(): ?string
    {
        return $this->contact ? $this->contact->getGender() : null;
    }

    public function setGender(string $gender): void
    {
        $this->contact->setGender($gender);
    }

    public function getCustomGender(): ?string
    {
        return $this->contact ? $this->contact->getCustomGender() : null;
    }

    public function setCustomGender(string $customGender): void
    {
        $this->contact->setCustomGender($customGender);
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->contact ? $this->contact->getBirthDate() : null;
    }

    public function setBirthDate(\DateTime $birthDate): void
    {
        $this->contact->setBirthDate($birthDate);
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->contact ? $this->contact->getPhone() : null;
    }

    public function setPhone(PhoneNumber $phone): void
    {
        $this->contact->setPhone($phone);
    }

    public function getJob(): ?string
    {
        return $this->contact ? $this->contact->getJob() : null;
    }

    public function setJob(string $job): void
    {
        $this->contact->setJob($job);
    }

    public function getPostAddress(): ?PostAddress
    {
        return $this->contact->getPostAddressModel();
    }

    public function getCityName(): ?string
    {
        return $this->contact ? $this->contact->getCityName() : null;
    }

    public function getPostalCode(): ?string
    {
        return $this->contact ? $this->contact->getPostalCode() : null;
    }

    public function hasSmsSubscriptionType(): bool
    {
        // contact does not have sms or email notifications yet
        return false;
    }

    public function hasEmailSubscriptionType(): bool
    {
        // contact does not have sms or email notifications yet
        return false;
    }

    public function isCertified(): bool
    {
        return false;
    }
}
