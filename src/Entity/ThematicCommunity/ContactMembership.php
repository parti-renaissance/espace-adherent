<?php

namespace App\Entity\ThematicCommunity;

use App\Address\AddressInterface;
use App\Address\PostAddressFactory;
use App\Entity\PostAddress;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;

#[ORM\Table(name: 'thematic_community_membership_contact')]
#[ORM\Entity]
class ContactMembership extends ThematicCommunityMembership
{
    public function getFirstName(): ?string
    {
        return $this->contact?->getFirstName();
    }

    public function setFirstName(string $firstName): void
    {
        $this->contact->setFirstName($firstName);
    }

    public function getLastName(): ?string
    {
        return $this->contact?->getLastName();
    }

    public function setLastName(string $lastName): void
    {
        $this->contact->setLastName($lastName);
    }

    public function getEmail(): ?string
    {
        return $this->contact?->getEmail();
    }

    public function setEmail(string $email): void
    {
        $this->contact->setEmail($email);
    }

    public function getGender(): ?string
    {
        return $this->contact?->getGender();
    }

    public function setGender(string $gender): void
    {
        $this->contact->setGender($gender);
    }

    public function getCustomGender(): ?string
    {
        return $this->contact?->getCustomGender();
    }

    public function setCustomGender(string $customGender): void
    {
        $this->contact->setCustomGender($customGender);
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->contact?->getBirthDate();
    }

    public function setBirthDate(?\DateTime $birthDate): void
    {
        $this->contact->setBirthDate($birthDate);
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->contact?->getPhone();
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->contact->setPhone($phone);
    }

    public function getPosition(): ?string
    {
        return $this->contact?->getPosition();
    }

    public function setPosition(string $position): void
    {
        $this->contact->setPosition($position);
    }

    public function getPostAddress(): PostAddress
    {
        return $this->contact ? $this->contact->getPostAddress() : PostAddress::createEmptyAddress();
    }

    public function setPostAddress(AddressInterface $address): void
    {
        $this->contact->updatePostAddress((new PostAddressFactory())->createFromAddress($address));
    }

    public function getCityName(): ?string
    {
        return $this->contact?->getCityName();
    }

    public function getPostalCode(): ?string
    {
        return $this->contact?->getPostalCode();
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
