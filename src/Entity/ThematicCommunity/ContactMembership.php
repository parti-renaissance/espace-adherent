<?php

namespace App\Entity\ThematicCommunity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;

/**
 * @ORM\Entity
 * @ORM\Table(name="thematic_community_membership_contact")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ContactMembership extends ThematicCommunityMembership
{
    /**
     * @var Contact
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ThematicCommunity\Contact", cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $contact;

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(Contact $contact): void
    {
        $this->contact = $contact;
    }

    public function getFirstName(): ?string
    {
        return $this->contact ? $this->contact->getFirstName() : null;
    }

    public function getLastName(): ?string
    {
        return $this->contact ? $this->contact->getLastName() : null;
    }

    public function getEmail(): ?string
    {
        return $this->contact ? $this->contact->getEmail() : null;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->contact ? $this->contact->getBirthDate() : null;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->contact ? $this->contact->getPhone() : null;
    }
}
