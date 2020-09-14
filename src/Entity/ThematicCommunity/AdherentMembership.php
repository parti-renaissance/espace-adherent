<?php

namespace App\Entity\ThematicCommunity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;

/**
 * @ORM\Entity
 * @ORM\Table(name="thematic_community_membership_adherent")
 *
 * @Algolia\Index(autoIndex=false)
 */
class AdherentMembership extends ThematicCommunityMembership
{
    /**
     * @var Adherent
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $adherent;

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

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

    public function getBirthDate(): ?\DateTime
    {
        return $this->adherent ? $this->adherent->getBirthDate() : null;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->adherent ? $this->adherent->getPhone() : null;
    }
}
