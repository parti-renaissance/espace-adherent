<?php

namespace App\Entity\ThematicCommunity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;

/**
 * @ORM\Entity
 * @ORM\Table(name="thematic_community_membership_elected_representative")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ElectedRepresentativeMembership extends ThematicCommunityMembership
{
    /**
     * @var ElectedRepresentative
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ElectedRepresentative\ElectedRepresentative")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $electedRepresentative;

    public function getElectedRepresentative(): ?ElectedRepresentative
    {
        return $this->electedRepresentative;
    }

    public function setElectedRepresentative(?ElectedRepresentative $electedRepresentative): void
    {
        $this->electedRepresentative = $electedRepresentative;
    }

    public function getFirstName(): ?string
    {
        return $this->electedRepresentative ? $this->electedRepresentative->getFirstName() : null;
    }

    public function getLastName(): ?string
    {
        return $this->electedRepresentative ? $this->electedRepresentative->getLastName() : null;
    }

    public function getEmail(): ?string
    {
        return $this->electedRepresentative ? $this->electedRepresentative->getEmailAddress() : null;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->electedRepresentative ? $this->electedRepresentative->getBirthDate() : null;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->electedRepresentative ? $this->electedRepresentative->getContactPhone() : null;
    }
}
