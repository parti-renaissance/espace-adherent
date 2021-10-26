<?php

namespace App\Entity\Pap;

use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\VoterRepository")
 * @ORM\Table(name="pap_voter")
 */
class Voter
{
    use EntityIdentityTrait;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $firstName = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $lastName = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $gender = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $birthdate = null;

    /**
     * @ORM\Column(length=10, nullable=true)
     */
    private ?string $votePlace = null;

    /**
     * @ORM\Column(length=5, nullable=true)
     */
    private ?string $source = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Address", inversedBy="voters")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?Address $address = null;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getVotePlace(): ?string
    {
        return $this->votePlace;
    }

    public function setVotePlace(?string $votePlace): void
    {
        $this->votePlace = $votePlace;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): void
    {
        $this->address = $address;
    }
}
